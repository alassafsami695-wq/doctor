<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['محمد العلي', 'سارة الأحمد', 'خالد المنصور', 'ليلى حسن', 'عمر الفاروق'];

        foreach ($names as $name) {
            Patient::create([
                'full_name' => $name,
                'phone' => '05' . rand(10000000, 99999999),
                'birth_date' => now()->subYears(rand(20, 50)),
                'gender' => rand(0, 1) ? 'male' : 'female',
                'medical_history' => 'لا يوجد حساسية، ضغط دم طبيعي.',
            ]);
        }
    }
}