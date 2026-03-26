<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_status',
        'is_resident_verified',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_resident_verified' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function residentProfile(): HasOne
    {
        return $this->hasOne(ResidentProfile::class);
    }

    public function officialProfile(): HasOne
    {
        return $this->hasOne(OfficialProfile::class);
    }

    public function isResident(): bool
    {
        return $this->role === 'resident';
    }

    public function isBarangayOfficial(): bool
    {
        return $this->role === 'barangay_official';
    }

    public function isCitySuperAdmin(): bool
    {
        return $this->role === 'city_super_admin';
    }

    public function officialRole(): ?string
    {
        return $this->officialProfile?->official_role;
    }

    public function barangayPermissions(): array
    {
        if (! $this->isBarangayOfficial()) {
            return [];
        }

        return config('portal.official_role_permissions.' . $this->officialRole(), []);
    }

    public function canAccessBarangayPermission(string $permission): bool
    {
        return in_array($permission, $this->barangayPermissions(), true);
    }

    public function preferredBarangayRoute(): string
    {
        return match ($this->officialRole()) {
            'verifier' => 'barangay.verifications.index',
            'encoder' => 'barangay.documents.index',
            'cashier' => 'barangay.payments.index',
            'release_officer' => 'barangay.releases.index',
            default => 'barangay.dashboard',
        };
    }
}
