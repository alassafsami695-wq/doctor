<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::first(); // ← نفس الطبيب

        if (!$doctor) {
            $this->command->warn('لم يتم العثور على طبيب.');
            return;
        }

        $partners = [
            [
                'name' => 'مخبر زهرة الشام',
                'type' => 'lab',
                'phone' => '0933111222',
                'contact' => 'أنس مندوب المخبر',
                'user_id' => $doctor->id // ← ربط بالطبيب
            ],
            [
                'name' => 'مخبر التطور الرقمي',
                'type' => 'lab',
                'phone' => '0944555666',
                'contact' => 'ياسين الفني',
                'user_id' => $doctor->id
            ],
            [
                'name' => 'شركة ميدكير للمعدات',
                'type' => 'company',
                'phone' => '011222333',
                'contact' => 'قسم المبيعات',
                'user_id' => $doctor->id
            ],
            [
                'name' => 'مستودع الشفاء السني',
                'type' => 'company',
                'phone' => '0999888777',
                'contact' => 'عمار المسؤول',
                'user_id' => $doctor->id
            ],
        ];

        foreach ($partners as $partnerData) {
            $partner = Partner::create($partnerData);

            for ($i = 0; $i < 4; $i++) {
                $randomDate = now()->subDays(rand(1, 45));
                
                $partner->logs()->create([
                    'type'             => collect(['order', 'payment', 'debt'])->random(),
                    'amount'           => rand(50000, 750000),
                    'note'             => 'عملية تجريبية رقم ' . ($i + 1),
                    'transaction_date' => $randomDate,
                    'created_at'       => $randomDate,
                    'updated_at'       => $randomDate,
                ]);
            }
        }

        $this->command->info('تم إنشاء الشركاء بنجاح.');
    }
}