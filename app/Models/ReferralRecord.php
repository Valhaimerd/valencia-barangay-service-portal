<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'referred_to',
        'referral_notes',
        'referral_status',
        'referred_at',
        'referred_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'referred_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }
}
