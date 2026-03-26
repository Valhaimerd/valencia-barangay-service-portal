<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CitySuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'citysuperadmin@valencia.local'],
            [
                'name' => 'City Super Admin',
                'password' => Hash::make('Admin12345!'),
                'role' => 'city_super_admin',
                'account_status' => 'active',
                'is_resident_verified' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
