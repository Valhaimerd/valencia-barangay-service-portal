<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\AssistanceRequestDetail;
use App\Models\DocumentRequestDetail;
use App\Models\RequestAttachment;
use App\Models\RequestStatusLog;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Support\RequestReferenceNumber;
use App\Support\ServiceRequestSchema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('residentProfile');

        $requests = $this->getResidentRequests($user->residentProfile?->id);

        return view('resident.requests.index', [
            'residentProfile' => $user->residentProfile,
            'requests' => $requests,
        ]);
    }

    public function create(): View
    {
        $services = ServiceType::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('resident.requests.create', [
            'services' => $services,
            'serviceType' => null,
            'schema' => null,
        ]);
    }

    public function createForService(ServiceType $serviceType): View
    {
        abort_unless($serviceType->is_active, 404);

        $services = ServiceType::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('resident.requests.create', [
            'services' => $services,
            'serviceType' => $serviceType,
            'schema' => ServiceRequestSchema::for($serviceType),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'service_type_code' => ['required', 'exists:service_types,code'],
        ]);

        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification',
        ]);

        abort_unless($user->residentProfile && $user->residentProfile->verification, 403);
        abort_unless($user->residentProfile->verification->status === 'verified', 403);

        $serviceType = ServiceType::query()
            ->where('code', $request->string('service_type_code')->toString())
            ->where('is_active', true)
            ->firstOrFail();

        $request->validate(ServiceRequestSchema::validationRules($serviceType));
        ServiceRequestSchema::assertBusinessRules($serviceType, $user->residentProfile->id);

        $residentProfile = $user->residentProfile;

        $serviceRequest = DB::transaction(function () use ($request, $user, $residentProfile, $serviceType): ServiceRequest {
            $serviceRequest = ServiceRequest::create([
                'reference_number' => RequestReferenceNumber::generate($serviceType, $residentProfile->barangay_id),
                'resident_profile_id' => $residentProfile->id,
                'service_type_id' => $serviceType->id,
                'barangay_id' => $residentProfile->barangay_id,
                'request_category' => $serviceType->category,
                'current_status' => 'submitted',
                'submitted_at' => now(),
                'latest_status_at' => now(),
            ]);

            if ($serviceType->category === 'document') {
                DocumentRequestDetail::create(array_merge(
                    ['request_id' => $serviceRequest->id],
                    ServiceRequestSchema::documentPayload($request, $serviceType)
                ));
            }

            if ($serviceType->category === 'assistance') {
                AssistanceRequestDetail::create(array_merge(
                    ['request_id' => $serviceRequest->id],
                    ServiceRequestSchema::assistancePayload($request, $serviceType)
                ));
            }

            foreach (ServiceRequestSchema::attachments($serviceType) as $attachmentKey => $attachmentSchema) {
                if (! $request->hasFile("attachments.{$attachmentKey}")) {
                    continue;
                }

                $uploadedFile = $request->file("attachments.{$attachmentKey}");
                $path = $uploadedFile->store("requests/{$serviceRequest->id}/attachments", 'public');

                RequestAttachment::create([
                    'request_id' => $serviceRequest->id,
                    'attachment_type' => $attachmentKey,
                    'file_path' => $path,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getClientMimeType(),
                    'file_size' => $uploadedFile->getSize(),
                    'uploaded_by_user_id' => $user->id,
                    'is_required' => (bool) ($attachmentSchema['required'] ?? false),
                    'review_status' => 'pending',
                ]);
            }

            foreach ($request->file('other_supporting_files', []) as $uploadedFile) {
                $path = $uploadedFile->store("requests/{$serviceRequest->id}/attachments", 'public');

                RequestAttachment::create([
                    'request_id' => $serviceRequest->id,
                    'attachment_type' => 'supporting_document',
                    'file_path' => $path,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getClientMimeType(),
                    'file_size' => $uploadedFile->getSize(),
                    'uploaded_by_user_id' => $user->id,
                    'is_required' => false,
                    'review_status' => 'pending',
                ]);
            }

            RequestStatusLog::create([
                'request_id' => $serviceRequest->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Request submitted by resident.',
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
            ]);

            return $serviceRequest;
        });

        return redirect()
            ->route('resident.requests.show', $serviceRequest->reference_number)
            ->with('success', 'Request submitted successfully.');
    }

    public function show(Request $request, string $referenceNumber): View
    {
        $user = $request->user()->load('residentProfile');

        abort_if(! $user->residentProfile, 404);

        $serviceRequest = ServiceRequest::query()
            ->with([
                'serviceType',
                'barangay',
                'documentDetail',
                'assistanceDetail',
                'attachments.uploadedBy',
                'generatedDocument',
                'releaseRecord',
                'paymentRecords.receivedBy',
                'statusLogs.actedBy',
                'referralRecords.referredBy',
            ])
            ->where('resident_profile_id', $user->residentProfile->id)
            ->where('reference_number', $referenceNumber)
            ->firstOrFail();

        return view('resident.requests.show', [
            'serviceRequest' => $serviceRequest,
            'schema' => ServiceRequestSchema::for($serviceRequest->serviceType),
            'summaryRows' => ServiceRequestSchema::summaryRows($serviceRequest),
        ]);
    }

    private function getResidentRequests(?int $residentProfileId): LengthAwarePaginator|Collection
    {
        if (! $residentProfileId) {
            return collect();
        }

        return ServiceRequest::query()
            ->with(['serviceType', 'barangay'])
            ->where('resident_profile_id', $residentProfileId)
            ->latest('created_at')
            ->paginate(10);
    }
}
