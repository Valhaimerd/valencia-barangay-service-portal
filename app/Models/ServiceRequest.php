<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'resident_profile_id',
        'service_type_id',
        'barangay_id',
        'request_category',
        'current_status',
        'submitted_at',
        'latest_status_at',
        'assigned_to_user_id',
        'reviewed_by_user_id',
        'approved_by_user_id',
        'rejected_by_user_id',
        'cancelled_by_user_id',
        'rejection_reason',
        'cancellation_reason',
        'internal_notes',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'latest_status_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function residentProfile(): BelongsTo
    {
        return $this->belongsTo(ResidentProfile::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function documentDetail(): HasOne
    {
        return $this->hasOne(DocumentRequestDetail::class, 'request_id');
    }

    public function assistanceDetail(): HasOne
    {
        return $this->hasOne(AssistanceRequestDetail::class, 'request_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RequestAttachment::class, 'request_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RequestStatusLog::class, 'request_id');
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class, 'request_id');
    }

    public function generatedDocument(): HasOne
    {
        return $this->hasOne(GeneratedDocument::class, 'request_id');
    }

    public function releaseRecord(): HasOne
    {
        return $this->hasOne(ReleaseRecord::class, 'request_id');
    }

    public function referralRecords(): HasMany
    {
        return $this->hasMany(ReferralRecord::class, 'request_id');
    }
}
