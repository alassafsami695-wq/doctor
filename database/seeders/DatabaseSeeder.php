<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ السوبر أدمن فقط
        User::create([
            'name' => 'Sami Super Admin',
            'email' => 'samialassaf333@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // ✅ بقية السيدرز (اختياري - يمكن تعليقها إذا لم تكن جاهزة)
         $this->call([
             ProcedureSeeder::class,
        //     PatientSeeder::class,
        //     PartnerSeeder::class,
        //     AppointmentSeeder::class,
        //     InvoiceSeeder::class,
         ]);
    }
}