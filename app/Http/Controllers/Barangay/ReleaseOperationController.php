<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\ReleaseRecord;
use App\Models\RequestStatusLog;
use App\Models\ServiceRequest;
use App\Support\AuditTrail;
use App\Support\ResidentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReleaseOperationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $barangayId = $user->officialProfile?->barangay_id;
        abort_unless($barangayId, 403, 'This official account has no barangay assignment.');

        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $search = trim($request->string('search')->toString());
        $likeOperator = $this->likeOperator();

        $releases = ServiceRequest::query()
            ->with([
                'residentProfile.user',
                'serviceType',
                'releaseRecord',
                'generatedDocument',
            ])
            ->where('barangay_id', $barangayId)
            ->whereIn('request_category', ['document', 'assistance'])
            ->when(
                $status !== '',
                fn ($query) => $query->where('current_status', $status),
                fn ($query) => $query->whereIn('current_status', ['ready_for_pickup', 'ready_for_claim', 'released'])
            )
            ->when($type !== '', fn ($query) => $query->where('request_category', $type))
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
            ->latest('latest_status_at')
            ->paginate(10)
            ->withQueryString();

        return view('barangay.releases.index', [
            'releases' => $releases,
            'selectedStatus' => $status,
            'selectedType' => $type,
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
            'generatedDocument',
            'releaseRecord.releasedBy',
            'statusLogs.actedBy',
        ]);

        $this->ensureSameBarangayReleaseQueue($user->officialProfile?->barangay_id, $serviceRequest);

        return view('barangay.releases.show', [
            'serviceRequest' => $serviceRequest,
            'residentProfile' => $serviceRequest->residentProfile,
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user()->load('officialProfile.barangay');

        $serviceRequest->load([
            'residentProfile.user',
            'releaseRecord',
            'generatedDocument',
        ]);

        $this->ensureSameBarangayReleaseQueue($user->officialProfile?->barangay_id, $serviceRequest);

        if (! in_array($serviceRequest->current_status, ['ready_for_pickup', 'ready_for_claim', 'released'], true)) {
            throw ValidationException::withMessages([
                'release' => 'Only ready-for-release requests can be recorded here.',
            ]);
        }

        $validated = $request->validate([
            'released_to_name' => ['required', 'string', 'max:255'],
            'released_to_relationship' => ['nullable', 'string', 'max:255'],
            'claimant_identification_notes' => ['nullable', 'string', 'max:2000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $oldStatus = $serviceRequest->current_status;

        DB::transaction(function () use ($validated, $user, $serviceRequest, $request, $oldStatus): void {
            $fromStatus = $serviceRequest->current_status;

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

            if ($serviceRequest->current_status !== 'released') {
                $serviceRequest->update([
                    'current_status' => 'released',
                    'latest_status_at' => now(),
                    'completed_at' => $serviceRequest->completed_at ?? now(),
                    'internal_notes' => $validated['remarks'] ?? $serviceRequest->internal_notes,
                ]);

                RequestStatusLog::create([
                    'request_id' => $serviceRequest->id,
                    'from_status' => $fromStatus,
                    'to_status' => 'released',
                    'remarks' => $validated['remarks'] ?: 'Request released to claimant.',
                    'acted_by_user_id' => $user->id,
                    'acted_at' => now(),
                ]);

                ResidentNotifier::requestStatusChanged($serviceRequest, $validated['remarks'] ?? null);
            } elseif (! empty($validated['remarks'])) {
                $serviceRequest->update([
                    'internal_notes' => $validated['remarks'],
                    'latest_status_at' => now(),
                ]);
            }

            AuditTrail::record(
                user: $user,
                action: 'request_released',
                subject: $serviceRequest,
                description: 'Release record updated.',
                oldValues: [
                    'status' => $oldStatus,
                    'released_to_name' => $serviceRequest->releaseRecord?->getOriginal('released_to_name'),
                ],
                newValues: [
                    'status' => $serviceRequest->current_status,
                    'released_to_name' => $validated['released_to_name'],
                ],
                request: $request,
            );
        });

        return redirect()
            ->route('barangay.releases.show', $serviceRequest)
            ->with('success', 'Release recorded successfully.');
    }

    private function ensureSameBarangayReleaseQueue(?int $officialBarangayId, ServiceRequest $serviceRequest): void
    {
        abort_unless($officialBarangayId, 403, 'This official account has no barangay assignment.');

        abort_unless(
            $serviceRequest->barangay_id === $officialBarangayId &&
            in_array($serviceRequest->request_category, ['document', 'assistance'], true),
            404
        );
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
