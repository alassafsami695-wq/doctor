<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::where('role', 'dentist')->first();

        if (!$doctor) {
            return;
        }

        $patients = [
            ['full_name' => 'محمد العلي', 'phone' => '0501234567', 'gender' => 'male', 'birth_date' => '1985-01-01'],
            ['full_name' => 'سارة الأحمد', 'phone' => '0501234568', 'gender' => 'female', 'birth_date' => '1990-05-15'],
            ['full_name' => 'خالد المنصور', 'phone' => '0501234569', 'gender' => 'male', 'birth_date' => '1978-03-20'],
            ['full_name' => 'ليلى حسن', 'phone' => '0501234570', 'gender' => 'female', 'birth_date' => '1982-11-10'],
            ['full_name' => 'عمر الفاروق', 'phone' => '0501234571', 'gender' => 'male', 'birth_date' => '1995-07-25'],
        ];

        foreach ($patients as $patient) {
            Patient::create(array_merge($patient, [
                'user_id' => $doctor->id, // ← ربط بالطبيب
            ]));
        }
    }
}