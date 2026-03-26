<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequestDetail;
use App\Models\GeneratedDocument;
use App\Models\PaymentRecord;
use App\Models\ReleaseRecord;
use App\Models\RequestStatusLog;
use App\Models\ServiceRequest;
use App\Support\AuditTrail;
use App\Support\DocumentNumber;
use App\Support\ResidentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $barangayId = $user->officialProfile?->barangay_id;
        abort_unless($barangayId, 403, 'This official account has no barangay assignment.');

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $documents = ServiceRequest::query()
            ->with(['residentProfile.user', 'serviceType', 'barangay', 'documentDetail'])
            ->where('barangay_id', $barangayId)
            ->where('request_category', 'document')
            ->when($status !== '', fn ($query) => $query->where('current_status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('reference_number', 'ilike', "%{$search}%")
                        ->orWhereHas('residentProfile', function ($profileQuery) use ($search) {
                            $profileQuery
                                ->where('first_name', 'ilike', "%{$search}%")
                                ->orWhere('middle_name', 'ilike', "%{$search}%")
                                ->orWhere('last_name', 'ilike', "%{$search}%")
                                ->orWhere('suffix', 'ilike', "%{$search}%");
                        });
                });
            })
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('barangay.documents.index', [
            'documents' => $documents,
            'selectedStatus' => $status,
            'search' => $search,
            'statusOptions' => config('portal.document_request_statuses'),
        ]);
    }

    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $serviceRequest->load([
            'residentProfile.user',
            'residentProfile.barangay',
            'serviceType',
            'documentDetail',
            'attachments.uploadedBy',
            'paymentRecords.receivedBy',
            'generatedDocument.preparedBy',
            'generatedDocument.printedBy',
            'releaseRecord.releasedBy',
            'statusLogs.actedBy',
        ]);

        $this->ensureSameBarangayDocument($user->officialProfile?->barangay_id, $serviceRequest);

        return view('barangay.documents.show', [
            'serviceRequest' => $serviceRequest,
            'residentProfile' => $serviceRequest->residentProfile,
            'documentDetail' => $serviceRequest->documentDetail,
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user()->load('officialProfile.barangay');

        $serviceRequest->load([
            'residentProfile.user',
            'serviceType',
            'documentDetail',
            'paymentRecords',
            'generatedDocument',
            'releaseRecord',
        ]);

        $this->ensureSameBarangayDocument($user->officialProfile?->barangay_id, $serviceRequest);

        $validated = $request->validate([
            'next_status' => [
                'required',
                Rule::in([
                    'under_review',
                    'approved',
                    'for_payment',
                    'for_printing',
                    'ready_for_pickup',
                    'released',
                    'rejected',
                    'cancelled',
                ]),
            ],
            'remarks' => ['nullable', 'string', 'max:5000'],

            'purpose' => ['nullable', 'string', 'max:2000'],
            'cedula_number' => ['nullable', 'string', 'max:255'],
            'cedula_date' => ['nullable', 'date'],
            'cedula_place' => ['nullable', 'string', 'max:255'],
            'years_of_residency' => ['nullable', 'integer', 'min:0', 'max:150'],
            'months_of_residency' => ['nullable', 'integer', 'min:0', 'max:11'],
            'oath_required' => ['nullable', 'boolean'],

            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'official_receipt_number' => ['nullable', 'string', 'max:255'],

            'released_to_name' => [
                Rule::requiredIf(fn () => $request->input('next_status') === 'released'),
                'nullable',
                'string',
                'max:255',
            ],
            'released_to_relationship' => ['nullable', 'string', 'max:255'],
            'claimant_identification_notes' => ['nullable', 'string', 'max:2000'],

            'rejection_reason' => [
                Rule::requiredIf(fn () => $request->input('next_status') === 'rejected'),
                'nullable',
                'string',
                'max:5000',
            ],
            'cancellation_reason' => [
                Rule::requiredIf(fn () => $request->input('next_status') === 'cancelled'),
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        if (
            $serviceRequest->serviceType->requires_payment &&
            in_array($validated['next_status'], ['for_printing', 'ready_for_pickup', 'released'], true) &&
            (
                empty($validated['payment_amount']) ||
                empty($validated['official_receipt_number'])
            )
        ) {
            throw ValidationException::withMessages([
                'official_receipt_number' => 'Payment amount and official receipt number are required before printing, pickup, or release.',
            ]);
        }

        $oldStatus = $serviceRequest->current_status;

        DB::transaction(function () use ($request, $validated, $user, $serviceRequest, $oldStatus): void {
            $nextStatus = $validated['next_status'];
            $fromStatus = $serviceRequest->current_status;

            $documentDetail = $serviceRequest->documentDetail ?: DocumentRequestDetail::create([
                'request_id' => $serviceRequest->id,
            ]);

            $documentDetail->update([
                'purpose' => $validated['purpose'] ?? $documentDetail->purpose,
                'cedula_number' => $validated['cedula_number'] ?? $documentDetail->cedula_number,
                'cedula_date' => $validated['cedula_date'] ?? $documentDetail->cedula_date,
                'cedula_place' => $validated['cedula_place'] ?? $documentDetail->cedula_place,
                'years_of_residency' => $validated['years_of_residency'] ?? $documentDetail->years_of_residency,
                'months_of_residency' => $validated['months_of_residency'] ?? $documentDetail->months_of_residency,
                'oath_required' => $request->boolean('oath_required'),
                'payment_amount' => $serviceRequest->serviceType->requires_payment
                    ? ($validated['payment_amount'] ?? $documentDetail->payment_amount)
                    : null,
                'official_receipt_number' => $serviceRequest->serviceType->requires_payment
                    ? ($validated['official_receipt_number'] ?? $documentDetail->official_receipt_number)
                    : null,
                'prepared_by_user_id' => in_array($nextStatus, ['for_printing', 'ready_for_pickup', 'released'], true)
                    ? $user->id
                    : $documentDetail->prepared_by_user_id,
                'printed_at' => in_array($nextStatus, ['ready_for_pickup', 'released'], true)
                    ? now()
                    : $documentDetail->printed_at,
            ]);

            if ($serviceRequest->serviceType->requires_payment) {
                $paymentRecord = PaymentRecord::query()->firstOrNew([
                    'request_id' => $serviceRequest->id,
                ]);

                $paymentRecord->fill([
                    'amount' => $validated['payment_amount'] ?? $paymentRecord->amount ?? 0,
                    'payment_status' => in_array($nextStatus, ['for_printing', 'ready_for_pickup', 'released'], true)
                        ? 'paid'
                        : 'pending',
                    'official_receipt_number' => $validated['official_receipt_number'] ?? $paymentRecord->official_receipt_number,
                    'paid_at' => in_array($nextStatus, ['for_printing', 'ready_for_pickup', 'released'], true)
                        ? ($paymentRecord->paid_at ?? now())
                        : null,
                    'received_by_user_id' => in_array($nextStatus, ['for_printing', 'ready_for_pickup', 'released'], true)
                        ? $user->id
                        : $paymentRecord->received_by_user_id,
                    'notes' => $validated['remarks'] ?? $paymentRecord->notes,
                ]);

                $paymentRecord->save();
            }

            if (in_array($nextStatus, ['for_printing', 'ready_for_pickup', 'released'], true)) {
                $generatedDocument = GeneratedDocument::query()->firstOrNew([
                    'request_id' => $serviceRequest->id,
                ]);

                $generatedDocument->fill([
                    'document_number' => $generatedDocument->document_number ?: DocumentNumber::generate($serviceRequest),
                    'generated_at' => $generatedDocument->generated_at ?? now(),
                    'prepared_by_user_id' => $generatedDocument->prepared_by_user_id ?? $user->id,
                    'printed_at' => in_array($nextStatus, ['ready_for_pickup', 'released'], true)
                        ? ($generatedDocument->printed_at ?? now())
                        : $generatedDocument->printed_at,
                    'printed_by_user_id' => in_array($nextStatus, ['ready_for_pickup', 'released'], true)
                        ? ($generatedDocument->printed_by_user_id ?? $user->id)
                        : $generatedDocument->printed_by_user_id,
                ]);

                $generatedDocument->save();
            }

            if ($nextStatus === 'released') {
                $releaseRecord = ReleaseRecord::query()->firstOrNew([
                    'request_id' => $serviceRequest->id,
                ]);

                $releaseRecord->fill([
                    'released_to_name' => $validated['released_to_name'],
                    'released_to_relationship' => $validated['released_to_relationship'] ?? null,
                    'released_at' => $releaseRecord->released_at ?? now(),
                    'released_by_user_id' => $user->id,
                    'claimant_identification_notes' => $validated['claimant_identification_notes'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                $releaseRecord->save();
            }

            $serviceRequest->update([
                'current_status' => $nextStatus,
                'latest_status_at' => now(),
                'assigned_to_user_id' => $serviceRequest->assigned_to_user_id ?? $user->id,
                'reviewed_by_user_id' => in_array($nextStatus, ['under_review', 'approved', 'for_payment', 'for_printing', 'ready_for_pickup', 'released'], true)
                    ? ($serviceRequest->reviewed_by_user_id ?? $user->id)
                    : $serviceRequest->reviewed_by_user_id,
                'approved_by_user_id' => in_array($nextStatus, ['approved', 'for_payment', 'for_printing', 'ready_for_pickup', 'released'], true)
                    ? ($serviceRequest->approved_by_user_id ?? $user->id)
                    : $serviceRequest->approved_by_user_id,
                'rejected_by_user_id' => $nextStatus === 'rejected' ? $user->id : null,
                'cancelled_by_user_id' => $nextStatus === 'cancelled' ? $user->id : null,
                'rejection_reason' => $nextStatus === 'rejected' ? $validated['rejection_reason'] : null,
                'cancellation_reason' => $nextStatus === 'cancelled' ? $validated['cancellation_reason'] : null,
                'internal_notes' => $validated['remarks'] ?? $serviceRequest->internal_notes,
                'completed_at' => $nextStatus === 'released' ? now() : $serviceRequest->completed_at,
                'cancelled_at' => $nextStatus === 'cancelled' ? now() : null,
            ]);

            RequestStatusLog::create([
                'request_id' => $serviceRequest->id,
                'from_status' => $fromStatus,
                'to_status' => $nextStatus,
                'remarks' => $validated['remarks'] ?: 'Document request status updated.',
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
            ]);

            if ($oldStatus !== $nextStatus) {
                ResidentNotifier::requestStatusChanged($serviceRequest, $validated['remarks'] ?? null);
            }

            AuditTrail::record(
                user: $user,
                action: 'document_request_updated',
                subject: $serviceRequest,
                description: 'Document request workflow updated.',
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $nextStatus],
                request: $request,
            );
        });

        return redirect()
            ->route('barangay.documents.show', $serviceRequest)
            ->with('success', 'Document request updated successfully.');
    }

    private function ensureSameBarangayDocument(?int $officialBarangayId, ServiceRequest $serviceRequest): void
    {
        abort_unless($officialBarangayId, 403, 'This official account has no barangay assignment.');

        abort_unless(
            $serviceRequest->barangay_id === $officialBarangayId &&
            $serviceRequest->request_category === 'document',
            404
        );
    }
}
