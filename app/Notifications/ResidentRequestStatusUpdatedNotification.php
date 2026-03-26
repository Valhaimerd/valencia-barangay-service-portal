<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResidentRequestStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $referenceNumber,
        public string $serviceName,
        public string $status,
        public ?string $remarks = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = "{$this->serviceName} ({$this->referenceNumber}) is now " .
            str($this->status)->replace('_', ' ')->lower() . '.';

        if ($this->remarks) {
            $message .= ' ' . $this->remarks;
        }

        return [
            'title' => 'Request Status Update',
            'message' => $message,
            'type' => match ($this->status) {
                'approved', 'ready_for_pickup', 'ready_for_claim', 'released', 'closed' => 'success',
                'needs_additional_documents', 'referred' => 'warning',
                'rejected', 'cancelled' => 'rejected',
                default => 'info',
            },
            'status' => $this->status,
            'reference_number' => $this->referenceNumber,
            'service_name' => $this->serviceName,
            'link' => route('resident.requests.show', $this->referenceNumber),
        ];
    }
}
