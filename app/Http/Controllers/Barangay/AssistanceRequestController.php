<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\AssistanceRequestDetail;
use App\Models\ReferralRecord;
use App\Models\ReleaseRecord;
use App\Models\RequestStatusLog;
use App\Models\ServiceRequest;
use App\Support\AuditTrail;
use App\Support\ResidentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssistanceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $barangayId = $user->officialProfile?->barangay_id;
        abort_unless($barangayId, 403, 'This official account has no barangay assignment.');

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $likeOperator = $this->likeOperator();

        $assistanceRequests = ServiceRequest::query()
            ->with(['residentProfile.user', 'serviceType', 'barangay', 'assistanceDetail'])
            ->where('barangay_id', $barangayId)
            ->where('request_category', 'assistance')
            ->when($status !== '', fn ($query) => $query->where('current_status', $status))
            ->when($search !== '', function ($query) use ($search, $likeOperator) {
                $query->where(function ($subQuery) use ($search, $likeOperator) {
                    $subQuery
                        ->where('reference_number', $likeOperator, "%{$search}%")
                        ->orWhereHas('residentProfile', function ($profileQuery) use ($search, $likeOperator) {
                            $profileQuery
                                ->where('first_name', $likeOperator, "%{$search}%")
                                ->orWhere('middle_name', $likeOperator, "%{$search}%")
                                ->orWhere('last_name', $likeOperator, "%{$search}%")
                                ->orWhere('suffix', $likeOperator, "%{$search}%");
                        });
                });
            })
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('barangay.assistance.index', [
            'assistanceRequests' => $assistanceRequests,
            'selectedStatus' => $status,
            'search' => $search,
            'statusOptions' => config('portal.assistance_request_statuses'),
        ]);
    }

    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $serviceRequest->load([
            'residentProfile.user',
            'residentProfile.barangay',
            'serviceType',
            'assistanceDetail',
            'attachments.uploadedBy',
            'referralRecords.referredBy',
            'releaseRecord.releasedBy',
            'statusLogs.actedBy',
        ]);

        $this->ensureSameBarangayAssistance($user->officialProfile?->barangay_id, $serviceRequest);

        return view('barangay.assistance.show', [
            'serviceRequest' => $serviceRequest,
            'residentProfile' => $serviceRequest->residentProfile,
            'assistanceDetail' => $serviceRequest->assistanceDetail,
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user()->load('officialProfile.barangay');

        $serviceRequest->load([
            'residentProfile.user',
            'serviceType',
            'assistanceDetail',
            'referralRecords',
            'releaseRecord',
        ]);

        $this->ensureSameBarangayAssistance($user->officialProfile?->barangay_id, $serviceRequest);

        $validated = $request->validate([
            'next_status' => [
                'required',
                Rule::in([
                    'under_review',
                    'needs_additional_documents',
                    'for_assessment',
                    'approved',
                    'referred',
                    'ready_for_claim',
                    'released',
                    'closed',
                    'rejected',
                    'cancelled',
                ]),
            ],
            'remarks' => ['nullable', 'string', 'max:5000'],

            'case_summary' => ['nullable', 'string', 'max:5000'],
            'requested_amount' => ['nullable', 'numeric', 'min:0'],
            'assessment_date' => ['nullable', 'date'],
            'assessment_notes' => ['nullable', 'string', 'max:5000'],
            'claimant_name' => ['nullable', 'string', 'max:255'],
            'relationship_to_beneficiary' => ['nullable', 'string', 'max:255'],

            'referred_to' => [
                Rule::requiredIf(fn () => $request->input('next_status') === 'referred'),
                'nullable',
                'string',
                'max:255',
            ],
            'referral_notes' => ['nullable', 'string', 'max:5000'],

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

        $oldStatus = $serviceRequest->current_status;

        DB::transaction(function () use ($request, $validated, $user, $serviceRequest, $oldStatus): void {
            $nextStatus = $validated['next_status'];
            $fromStatus = $serviceRequest->current_status;

            $assistanceDetail = $serviceRequest->assistanceDetail ?: AssistanceRequestDetail::create([
                'request_id' => $serviceRequest->id,
            ]);

            $assistanceDetail->update([
                'case_summary' => $validated['case_summary'] ?? $assistanceDetail->case_summary,
                'requested_amount' => array_key_exists('requested_amount', $validated)
                    ? $validated['requested_amount']
                    : $assistanceDetail->requested_amount,
                'assessment_date' => $validated['assessment_date'] ?? $assistanceDetail->assessment_date,
                'assessment_notes' => $validated['assessment_notes'] ?? $assistanceDetail->assessment_notes,
                'claimant_name' => $validated['claimant_name'] ?? $assistanceDetail->claimant_name,
                'relationship_to_beneficiary' => $validated['relationship_to_beneficiary'] ?? $assistanceDetail->relationship_to_beneficiary,
                'referral_destination' => $nextStatus === 'referred'
                    ? $validated['referred_to']
                    : $assistanceDetail->referral_destination,
            ]);

            if ($nextStatus === 'referred') {
                ReferralRecord::create([
                    'request_id' => $serviceRequest->id,
                    'referred_to' => $validated['referred_to'],
                    'referral_notes' => $validated['referral_notes'] ?? null,
                    'referral_status' => 'referred',
                    'referred_at' => now(),
                    'referred_by_user_id' => $user->id,
                ]);
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
                'reviewed_by_user_id' => in_array($nextStatus, [
                    'under_review',
                    'needs_additional_documents',
                    'for_assessment',
                    'approved',
                    'referred',
                    'ready_for_claim',
                    'released',
                    'closed',
                ], true)
                    ? ($serviceRequest->reviewed_by_user_id ?? $user->id)
                    : $serviceRequest->reviewed_by_user_id,
                'approved_by_user_id' => in_array($nextStatus, [
                    'approved',
                    'referred',
                    'ready_for_claim',
                    'released',
                    'closed',
                ], true)
                    ? ($serviceRequest->approved_by_user_id ?? $user->id)
                    : $serviceRequest->approved_by_user_id,
                'rejected_by_user_id' => $nextStatus === 'rejected' ? $user->id : null,
                'cancelled_by_user_id' => $nextStatus === 'cancelled' ? $user->id : null,
                'rejection_reason' => $nextStatus === 'rejected' ? $validated['rejection_reason'] : null,
                'cancellation_reason' => $nextStatus === 'cancelled' ? $validated['cancellation_reason'] : null,
                'internal_notes' => $validated['remarks'] ?? $serviceRequest->internal_notes,
                'completed_at' => in_array($nextStatus, ['released', 'closed'], true) ? now() : $serviceRequest->completed_at,
                'cancelled_at' => $nextStatus === 'cancelled' ? now() : null,
            ]);

            RequestStatusLog::create([
                'request_id' => $serviceRequest->id,
                'from_status' => $fromStatus,
                'to_status' => $nextStatus,
                'remarks' => $validated['remarks'] ?: 'Assistance request status updated.',
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
            ]);

            if ($oldStatus !== $nextStatus) {
                ResidentNotifier::requestStatusChanged($serviceRequest, $validated['remarks'] ?? null);
            }

            AuditTrail::record(
                user: $user,
                action: 'assistance_request_updated',
                subject: $serviceRequest,
                description: 'Assistance request workflow updated.',
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $nextStatus],
                request: $request,
            );
        });

        return redirect()
            ->route('barangay.assistance.show', $serviceRequest)
            ->with('success', 'Assistance request updated successfully.');
    }

    private function ensureSameBarangayAssistance(?int $officialBarangayId, ServiceRequest $serviceRequest): void
    {
        abort_unless($officialBarangayId, 403, 'This official account has no barangay assignment.');

        abort_unless(
            $serviceRequest->barangay_id === $officialBarangayId &&
            $serviceRequest->request_category === 'assistance',
            404
        );
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
