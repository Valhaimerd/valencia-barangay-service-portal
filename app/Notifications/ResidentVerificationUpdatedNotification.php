<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResidentVerificationUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $status,
        public ?string $barangayName = null,
        public ?string $correctionNotes = null,
        public ?string $rejectionReason = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = match ($this->status) {
            'verified' => 'Your resident verification was approved.',
            'needs_correction' => 'Your resident verification needs correction.',
            'rejected' => 'Your resident verification was rejected.',
            default => 'Your resident verification status was updated.',
        };

        if ($this->barangayName) {
            $message .= ' Barangay: ' . $this->barangayName . '.';
        }

        if ($this->status === 'needs_correction' && $this->correctionNotes) {
            $message .= ' Notes: ' . $this->correctionNotes;
        }

        if ($this->status === 'rejected' && $this->rejectionReason) {
            $message .= ' Reason: ' . $this->rejectionReason;
        }

        return [
            'title' => 'Resident Verification Update',
            'message' => $message,
            'type' => match ($this->status) {
                'verified' => 'success',
                'needs_correction' => 'warning',
                'rejected' => 'rejected',
                default => 'info',
            },
            'status' => $this->status,
            'link' => match ($this->status) {
                'verified' => route('resident.dashboard'),
                'needs_correction' => route('resident.verification.correction'),
                'rejected' => route('resident.verification.rejected'),
                default => route('resident.verification.pending'),
            },
        ];
    }
}
