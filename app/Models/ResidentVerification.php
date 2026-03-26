<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResidentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_profile_id',
        'verification_method',
        'identity_document_label',
        'identity_document_number',
        'proof_of_residency_label',
        'status',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'reviewed_by_user_id',
        'correction_notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function residentProfile(): BelongsTo
    {
        return $this->belongsTo(ResidentProfile::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ResidentVerificationFile::class);
    }
}
