<?php

namespace App\Support;

use App\Models\ResidentVerification;
use App\Models\ServiceRequest;
use App\Notifications\ResidentRequestStatusUpdatedNotification;
use App\Notifications\ResidentVerificationUpdatedNotification;

class ResidentNotifier
{
    public static function verificationStatusChanged(ResidentVerification $residentVerification): void
    {
        $residentVerification->loadMissing('residentProfile.user', 'residentProfile.barangay');

        $user = $residentVerification->residentProfile?->user;

        if (! $user) {
            return;
        }

        $user->notify(new ResidentVerificationUpdatedNotification(
            status: $residentVerification->status,
            barangayName: $residentVerification->residentProfile?->barangay?->name,
            correctionNotes: $residentVerification->correction_notes,
            rejectionReason: $residentVerification->rejection_reason,
        ));
    }

    public static function requestStatusChanged(ServiceRequest $serviceRequest, ?string $remarks = null): void
    {
        $serviceRequest->loadMissing('residentProfile.user', 'serviceType');

        $user = $serviceRequest->residentProfile?->user;

        if (! $user) {
            return;
        }

        $user->notify(new ResidentRequestStatusUpdatedNotification(
            referenceNumber: $serviceRequest->reference_number,
            serviceName: $serviceRequest->serviceType?->name ?? 'Service Request',
            status: $serviceRequest->current_status,
            remarks: $remarks,
        ));
    }
}
