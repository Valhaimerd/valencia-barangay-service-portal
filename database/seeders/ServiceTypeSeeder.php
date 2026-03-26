<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'barangay_clearance',
                'name' => 'Barangay Clearance',
                'category' => 'document',
                'requires_payment' => true,
                'is_active' => true,
            ],
            [
                'code' => 'certificate_of_residency',
                'name' => 'Certificate of Residency',
                'category' => 'document',
                'requires_payment' => true,
                'is_active' => true,
            ],
            [
                'code' => 'certificate_of_indigency',
                'name' => 'Certificate of Indigency',
                'category' => 'document',
                'requires_payment' => false,
                'is_active' => true,
            ],
            [
                'code' => 'first_time_jobseeker_certification',
                'name' => 'First-Time Jobseeker Certification',
                'category' => 'document',
                'requires_payment' => false,
                'is_active' => true,
            ],
            [
                'code' => 'medical_assistance',
                'name' => 'Medical Assistance',
                'category' => 'assistance',
                'requires_payment' => false,
                'is_active' => true,
            ],
            [
                'code' => 'educational_assistance',
                'name' => 'Educational Assistance',
                'category' => 'assistance',
                'requires_payment' => false,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            ServiceType::query()->updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
