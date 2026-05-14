<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::where('user_id', auth()->id())->get(); // ← فقط مرضى الطبيب المسجل
        $doctor = User::where('role', 'dentist')->first();

        if ($patients->count() > 0 && $doctor) {
            foreach ($patients as $patient) {
                Appointment::create([
                    'patient_id' => $patient->id,
                    'user_id'    => $doctor->id,
                    'appointment_date' => now()->addDays(rand(1, 10))->setHour(rand(10, 18))->setMinute(0),
                    'status' => collect(['pending', 'completed', 'cancelled'])->random(),
                    'notes' => 'مراجعة دورية للمريض ' . $patient->full_name,
                ]);
            }
        }
    }
}