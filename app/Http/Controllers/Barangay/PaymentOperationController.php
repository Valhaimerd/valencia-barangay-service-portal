<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequestDetail;
use App\Models\PaymentRecord;
use App\Models\RequestStatusLog;
use App\Models\ServiceRequest;
use App\Support\AuditTrail;
use App\Support\ResidentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentOperationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $barangayId = $user->officialProfile?->barangay_id;
        abort_unless($barangayId, 403, 'This official account has no barangay assignment.');

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $payments = ServiceRequest::query()
            ->with(['residentProfile.user', 'serviceType', 'documentDetail', 'paymentRecords'])
            ->where('barangay_id', $barangayId)
            ->where('request_category', 'document')
            ->whereHas('serviceType', fn ($query) => $query->where('requires_payment', true))
            ->when(
                $status !== '',
                fn ($query) => $query->where('current_status', $status),
                fn ($query) => $query->whereIn('current_status', ['approved', 'for_payment', 'for_printing'])
            )
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
            ->latest('latest_status_at')
            ->paginate(10)
            ->withQueryString();

        return view('barangay.payments.index', [
            'payments' => $payments,
            'selectedStatus' => $status,
            'search' => $search,
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
            'paymentRecords.receivedBy',
            'statusLogs.actedBy',
        ]);

        $this->ensureSameBarangayPayableDocument($user->officialProfile?->barangay_id, $serviceRequest);

        return view('barangay.payments.show', [
            'serviceRequest' => $serviceRequest,
            'residentProfile' => $serviceRequest->residentProfile,
            'documentDetail' => $serviceRequest->documentDetail,
            'latestPayment' => $serviceRequest->paymentRecords->sortByDesc('paid_at')->first(),
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
        ]);

        $this->ensureSameBarangayPayableDocument($user->officialProfile?->barangay_id, $serviceRequest);

        $validated = $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'official_receipt_number' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $oldStatus = $serviceRequest->current_status;

        DB::transaction(function () use ($validated, $user, $serviceRequest, $request, $oldStatus): void {
            $fromStatus = $serviceRequest->current_status;

            $paymentRecord = PaymentRecord::query()->firstOrNew([
                'request_id' => $serviceRequest->id,
            ]);

            $paymentRecord->fill([
                'amount' => $validated['payment_amount'],
                'payment_status' => 'paid',
                'official_receipt_number' => $validated['official_receipt_number'],
                'paid_at' => $paymentRecord->paid_at ?? now(),
                'received_by_user_id' => $user->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            $paymentRecord->save();

            $documentDetail = $serviceRequest->documentDetail ?: DocumentRequestDetail::create([
                'request_id' => $serviceRequest->id,
            ]);

            $documentDetail->update([
                'payment_amount' => $validated['payment_amount'],
                'official_receipt_number' => $validated['official_receipt_number'],
            ]);

            if ($serviceRequest->current_status !== 'for_printing') {
                $serviceRequest->update([
                    'current_status' => 'for_printing',
                    'latest_status_at' => now(),
                    'internal_notes' => $validated['notes'] ?? $serviceRequest->internal_notes,
                ]);

                RequestStatusLog::create([
                    'request_id' => $serviceRequest->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'for_printing',
                    'remarks' => $validated['notes'] ?: 'Payment confirmed and request moved to printing.',
                    'acted_by_user_id' => $user->id,
                    'acted_at' => now(),
                ]);

                ResidentNotifier::requestStatusChanged($serviceRequest, $validated['notes'] ?? null);
            } elseif (! empty($validated['notes'])) {
                $serviceRequest->update([
                    'internal_notes' => $validated['notes'],
                    'latest_status_at' => now(),
                ]);
            }

            AuditTrail::record(
                user: $user,
                action: 'payment_recorded',
                subject: $serviceRequest,
                description: 'Payment recorded for document request.',
                oldValues: ['status' => $oldStatus],
                newValues: [
                    'status' => $serviceRequest->current_status,
                    'payment_amount' => $validated['payment_amount'],
                    'official_receipt_number' => $validated['official_receipt_number'],
                ],
                request: $request,
            );
        });

        return redirect()
            ->route('barangay.payments.show', $serviceRequest)
            ->with('success', 'Payment recorded successfully.');
    }

    private function ensureSameBarangayPayableDocument(?int $officialBarangayId, ServiceRequest $serviceRequest): void
    {
        abort_unless($officialBarangayId, 403, 'This official account has no barangay assignment.');

        abort_unless(
            $serviceRequest->barangay_id === $officialBarangayId &&
            $serviceRequest->request_category === 'document' &&
            $serviceRequest->serviceType?->requires_payment,
            404
        );
    }
}
