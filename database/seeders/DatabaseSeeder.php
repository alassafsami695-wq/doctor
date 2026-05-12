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
        // 1. السوبر أدمن (Sami Al-Assaf)
        User::create([
            'name' => 'Sami Super Admin',
            'email' => 'admin@clinic.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        // 2. الطبيب
        $doctor = User::create([
            'name' => 'Dr. Ahmad',
            'email' => 'doctor@clinic.com',
            'password' => Hash::make('password123'),
            'role' => 'dentist',
        ]);

        // 3. الاشتراك
        Subscription::create([
            'user_id' => $doctor->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(12),
            'status' => 'active',
            'months_duration' => 12
        ]);

        // 4. تشغيل جميع الملفات بالترتيب
        $this->call([
            ProcedureSeeder::class, // الإجراءات أولاً
            PatientSeeder::class,   // المرضى
            PartnerSeeder::class,   // المخابر والشركات
            AppointmentSeeder::class, // المواعيد
            InvoiceSeeder::class,    // الفواتير
        ]);
    }
}