<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. السوبر أدمن
        User::create([
            'name' => 'Sami Super Admin',
            'email' => 'admin@clinic.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 2. الطبيب - ✅ مفعل ومؤكد
        $doctor = User::create([
            'name' => 'Dr. Ahmad',
            'email' => 'doctor@clinic.com',
            'password' => Hash::make('password123'),
            'role' => 'dentist',
            'is_active' => true,
            'email_verified_at' => now(),
            'phone' => '0500000000',
            'clinic_address' => 'Main Clinic',
        ]);

        // 3. الاشتراك
        Subscription::create([
            'user_id' => $doctor->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(12),
            'status' => 'active',
            'months_duration' => 12,
            'price' => 500000,
        ]);

        // 4. بقية السيدرز
        $this->call([
            ProcedureSeeder::class,
            PatientSeeder::class,
            PartnerSeeder::class,
            AppointmentSeeder::class,
            InvoiceSeeder::class,
        ]);
    }
}