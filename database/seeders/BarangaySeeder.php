<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $barangays = [
            'Poblacion',
            'Lumbo',
            'Batangan',
            'Bagontaas',
            'Banlag',
            'Lurogan',
            'Tonganton',
            'Guinoyurar',
            'Lilingayon',
            'Sinayawan',
        ];

        foreach ($barangays as $barangayName) {
            Barangay::query()->updateOrCreate(
                ['name' => $barangayName],
                [
                    'slug' => Str::slug($barangayName),
                    'is_active' => true,
                ]
            );
        }
    }
}
