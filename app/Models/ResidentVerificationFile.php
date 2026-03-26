<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentVerificationFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_verification_id',
        'file_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'review_status',
        'reviewer_notes',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function residentVerification(): BelongsTo
    {
        return $this->belongsTo(ResidentVerification::class);
    }
}
