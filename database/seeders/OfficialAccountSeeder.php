<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\OfficialProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OfficialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@valencia-portal.test')->firstOrFail();

        $barangays = Barangay::query()->orderBy('name')->get();

        foreach ($barangays as $barangay) {
            $slug = Str::slug($barangay->name);

            $user = User::query()->updateOrCreate(
                ['email' => "admin.{$slug}@valencia-portal.test"],
                [
                    'name' => "{$barangay->name} Barangay Admin",
                    'password' => Hash::make('BarangayAdmin123!'),
                    'role' => 'barangay_official',
                    'account_status' => 'active',
                    'is_resident_verified' => false,
                    'email_verified_at' => now(),
                ]
            );

            $employeeCode = 'BRGY-' . strtoupper(Str::slug($barangay->name, '-')) . '-ADMIN';

            $officialProfile = OfficialProfile::query()
                ->where('user_id', $user->id)
                ->orWhere('employee_code', $employeeCode)
                ->first() ?? new OfficialProfile();

            $officialProfile->fill([
                'user_id' => $user->id,
                'barangay_id' => $barangay->id,
                'official_role' => 'barangay_admin',
                'employee_code' => $employeeCode,
                'assigned_by_user_id' => $superAdmin->id,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $officialProfile->save();
        }

        $poblacion = Barangay::query()->where('name', 'Poblacion')->firstOrFail();

        $roleAccounts = [
            [
                'email' => 'verifier.poblacion@valencia-portal.test',
                'name' => 'Poblacion Verifier',
                'official_role' => 'verifier',
                'employee_code' => 'POBLACION-VERIFY',
            ],
            [
                'email' => 'encoder.poblacion@valencia-portal.test',
                'name' => 'Poblacion Encoder',
                'official_role' => 'encoder',
                'employee_code' => 'POBLACION-ENCODER',
            ],
            [
                'email' => 'cashier.poblacion@valencia-portal.test',
                'name' => 'Poblacion Cashier',
                'official_role' => 'cashier',
                'employee_code' => 'POBLACION-CASHIER',
            ],
            [
                'email' => 'release.poblacion@valencia-portal.test',
                'name' => 'Poblacion Release Officer',
                'official_role' => 'release_officer',
                'employee_code' => 'POBLACION-RELEASE',
            ],
        ];

        foreach ($roleAccounts as $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('OfficialRole123!'),
                    'role' => 'barangay_official',
                    'account_status' => 'active',
                    'is_resident_verified' => false,
                    'email_verified_at' => now(),
                ]
            );

            $officialProfile = OfficialProfile::query()
                ->where('user_id', $user->id)
                ->orWhere('employee_code', $account['employee_code'])
                ->first() ?? new OfficialProfile();

            $officialProfile->fill([
                'user_id' => $user->id,
                'barangay_id' => $poblacion->id,
                'official_role' => $account['official_role'],
                'employee_code' => $account['employee_code'],
                'assigned_by_user_id' => $superAdmin->id,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $officialProfile->save();
        }
    }
}
