<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\ResidentVerification;
use App\Support\AuditTrail;
use App\Support\ResidentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResidentVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $barangayId = $user->officialProfile?->barangay_id;
        abort_unless($barangayId, 403, 'This official account has no barangay assignment.');

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $likeOperator = $this->likeOperator();

        $verifications = ResidentVerification::query()
            ->with(['residentProfile.user', 'residentProfile.barangay'])
            ->whereHas('residentProfile', function ($query) use ($barangayId, $search, $likeOperator) {
                $query->where('barangay_id', $barangayId);

                if ($search !== '') {
                    $query->where(function ($subQuery) use ($search, $likeOperator) {
                        $subQuery
                            ->where('first_name', $likeOperator, "%{$search}%")
                            ->orWhere('middle_name', $likeOperator, "%{$search}%")
                            ->orWhere('last_name', $likeOperator, "%{$search}%")
                            ->orWhere('suffix', $likeOperator, "%{$search}%");
                    });
                }
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('barangay.verifications.index', [
            'verifications' => $verifications,
            'selectedStatus' => $status,
            'search' => $search,
            'statusOptions' => config('portal.resident_verification_statuses'),
        ]);
    }

    public function show(Request $request, ResidentVerification $residentVerification): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $residentVerification->load([
            'residentProfile.user',
            'residentProfile.barangay',
            'files',
            'reviewer',
        ]);

        $this->ensureSameBarangay($user->officialProfile?->barangay_id, $residentVerification);

        return view('barangay.verifications.show', [
            'residentVerification' => $residentVerification,
            'residentProfile' => $residentVerification->residentProfile,
            'reviewer' => $residentVerification->reviewer,
        ]);
    }

    public function update(Request $request, ResidentVerification $residentVerification): RedirectResponse
    {
        $user = $request->user()->load('officialProfile.barangay');

        $residentVerification->load([
            'residentProfile.user',
            'residentProfile.barangay',
            'files',
        ]);

        $this->ensureSameBarangay($user->officialProfile?->barangay_id, $residentVerification);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['verified', 'needs_correction', 'rejected'])],
            'correction_notes' => [
                Rule::requiredIf(fn () => $request->input('decision') === 'needs_correction'),
                'nullable',
                'string',
                'max:5000',
            ],
            'rejection_reason' => [
                Rule::requiredIf(fn () => $request->input('decision') === 'rejected'),
                'nullable',
                'string',
                'max:5000',
            ],
            'file_statuses' => ['nullable', 'array'],
            'file_statuses.*' => ['nullable', Rule::in(['pending', 'accepted', 'rejected'])],
            'file_notes' => ['nullable', 'array'],
            'file_notes.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $decision = $validated['decision'];
        $oldStatus = $residentVerification->status;

        $resolvedStatuses = [];
        foreach ($residentVerification->files as $file) {
            $resolvedStatuses[$file->id] = $validated['file_statuses'][$file->id] ?? $file->review_status;
        }

        if ($decision === 'verified' && in_array('rejected', $resolvedStatuses, true)) {
            throw ValidationException::withMessages([
                'decision' => 'A verification with rejected file items cannot be marked as verified.',
            ]);
        }

        DB::transaction(function () use ($validated, $decision, $user, $residentVerification, $request, $oldStatus): void {
            foreach ($residentVerification->files as $file) {
                $newStatus = $validated['file_statuses'][$file->id] ?? $file->review_status;

                if ($decision === 'verified' && $newStatus === 'pending') {
                    $newStatus = 'accepted';
                }

                $file->update([
                    'review_status' => $newStatus,
                    'reviewer_notes' => $validated['file_notes'][$file->id] ?? $file->reviewer_notes,
                ]);
            }

            $residentVerification->update([
                'status' => $decision,
                'reviewed_at' => now(),
                'approved_at' => $decision === 'verified' ? now() : null,
                'reviewed_by_user_id' => $user->id,
                'correction_notes' => $decision === 'needs_correction' ? $validated['correction_notes'] : null,
                'rejection_reason' => $decision === 'rejected' ? $validated['rejection_reason'] : null,
            ]);

            $residentVerification->residentProfile->user->update([
                'is_resident_verified' => $decision === 'verified',
            ]);

            ResidentNotifier::verificationStatusChanged($residentVerification);

            AuditTrail::record(
                user: $user,
                action: 'resident_verification_updated',
                subject: $residentVerification,
                description: 'Resident verification status updated.',
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $decision],
                request: $request,
            );
        });

        return redirect()
            ->route('barangay.verifications.show', $residentVerification)
            ->with('success', 'Resident verification updated successfully.');
    }

    private function ensureSameBarangay(?int $officialBarangayId, ResidentVerification $residentVerification): void
    {
        abort_unless($officialBarangayId, 403, 'This official account has no barangay assignment.');

        abort_unless(
            $residentVerification->residentProfile?->barangay_id === $officialBarangayId,
            404
        );
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
