<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResidentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'barangay_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'birth_date',
        'birth_place',
        'civil_status',
        'mobile_number',
        'occupation',
        'citizenship',
        'current_address_line',
        'current_municipality',
        'current_province',
        'permanent_address_line',
        'permanent_barangay_name',
        'permanent_municipality',
        'permanent_province',
        'profile_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function verification(): HasOne
    {
        return $this->hasOne(ResidentVerification::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])->filter()->implode(' '));
    }
}
