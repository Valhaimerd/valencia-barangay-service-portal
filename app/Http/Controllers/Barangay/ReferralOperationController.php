<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\RequestStatusLog;
use App\Models\ServiceRequest;
use App\Support\AuditTrail;
use App\Support\ResidentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReferralOperationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $barangayId = $user->officialProfile?->barangay_id;
        abort_unless($barangayId, 403, 'This official account has no barangay assignment.');

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $referrals = ServiceRequest::query()
            ->with(['residentProfile.user', 'serviceType', 'assistanceDetail', 'referralRecords'])
            ->where('barangay_id', $barangayId)
            ->where('request_category', 'assistance')
            ->when(
                $status !== '',
                fn ($query) => $query->where('current_status', $status),
                fn ($query) => $query->whereIn('current_status', ['referred', 'closed'])
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

        return view('barangay.referrals.index', [
            'referrals' => $referrals,
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
            'assistanceDetail',
            'referralRecords.referredBy',
            'statusLogs.actedBy',
        ]);

        $this->ensureSameBarangayReferredAssistance($user->officialProfile?->barangay_id, $serviceRequest);

        return view('barangay.referrals.show', [
            'serviceRequest' => $serviceRequest,
            'residentProfile' => $serviceRequest->residentProfile,
            'latestReferral' => $serviceRequest->referralRecords->sortByDesc('referred_at')->first(),
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user()->load('officialProfile.barangay');

        $serviceRequest->load([
            'residentProfile.user',
            'referralRecords',
        ]);

        $this->ensureSameBarangayReferredAssistance($user->officialProfile?->barangay_id, $serviceRequest);

        $latestReferral = $serviceRequest->referralRecords->sortByDesc('referred_at')->first();

        if (! $latestReferral) {
            throw ValidationException::withMessages([
                'referral' => 'No referral record exists for this request.',
            ]);
        }

        $validated = $request->validate([
            'referral_status' => ['required', 'in:completed,cancelled'],
            'follow_up_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $oldStatus = $serviceRequest->current_status;

        DB::transaction(function () use ($validated, $user, $serviceRequest, $latestReferral, $request, $oldStatus): void {
            $fromStatus = $serviceRequest->current_status;

            $latestReferral->update([
                'referral_status' => $validated['referral_status'],
                'referral_notes' => $validated['follow_up_notes'] ?? $latestReferral->referral_notes,
            ]);

            if ($serviceRequest->current_status !== 'closed') {
                $serviceRequest->update([
                    'current_status' => 'closed',
                    'latest_status_at' => now(),
                    'completed_at' => now(),
                    'internal_notes' => $validated['follow_up_notes'] ?? $serviceRequest->internal_notes,
                ]);

                RequestStatusLog::create([
                    'request_id' => $serviceRequest->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'closed',
                    'remarks' => 'Referral outcome recorded: ' . ucfirst($validated['referral_status']) . '.',
                    'acted_by_user_id' => $user->id,
                    'acted_at' => now(),
                ]);

                ResidentNotifier::requestStatusChanged($serviceRequest, $validated['follow_up_notes'] ?? null);
            } elseif (! empty($validated['follow_up_notes'])) {
                $serviceRequest->update([
                    'internal_notes' => $validated['follow_up_notes'],
                    'latest_status_at' => now(),
                ]);
            }

            AuditTrail::record(
                user: $user,
                action: 'referral_outcome_recorded',
                subject: $serviceRequest,
                description: 'Referral outcome recorded.',
                oldValues: ['status' => $oldStatus, 'referral_status' => $latestReferral->getOriginal('referral_status')],
                newValues: ['status' => $serviceRequest->current_status, 'referral_status' => $validated['referral_status']],
                request: $request,
            );
        });

        return redirect()
            ->route('barangay.referrals.show', $serviceRequest)
            ->with('success', 'Referral outcome recorded successfully.');
    }

    private function ensureSameBarangayReferredAssistance(?int $officialBarangayId, ServiceRequest $serviceRequest): void
    {
        abort_unless($officialBarangayId, 403, 'This official account has no barangay assignment.');

        abort_unless(
            $serviceRequest->barangay_id === $officialBarangayId &&
            $serviceRequest->request_category === 'assistance',
            404
        );
    }
}
