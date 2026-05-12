<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. جلب الطبيب لربط الشركاء بحسابه (لأن حقل user_id مطلوب في التهجير)
        $doctor = User::where('role', 'dentist')->first();

        // في حال عدم وجود طبيب، نتوقف لتجنب خطأ SQL
        if (!$doctor) {
            $this->command->warn('لم يتم العثور على طبيب، يرجى التأكد من تشغيل DatabaseSeeder أولاً.');
            return;
        }

        // 2. تعريف بيانات الشركاء (مخابر وشركات)
        $partners = [
            [
                'name' => 'مخبر زهرة الشام',
                'type' => 'lab',
                'phone' => '0933111222',
                'contact' => 'أنس مندوب المخبر',
                'user_id' => $doctor->id
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
            // إنشاء الشريك
            $partner = Partner::create($partnerData);

            // 3. إنشاء حركات مالية (Logs) وهمية لكل شريك
            // تم إضافة transaction_date لحل مشكلة Field doesn't have a default value
            for ($i = 0; $i < 4; $i++) {
                $randomDate = now()->subDays(rand(1, 45)); // تاريخ عشوائي خلال آخر شهر ونصف
                
                $partner->logs()->create([
                    'type'             => collect(['order', 'payment', 'debt'])->random(),
                    'amount'           => rand(50000, 750000), // مبالغ بين 50 ألف و 750 ألف
                    'note'             => 'عملية تجريبية رقم ' . ($i + 1) . ' - تم التوليد آلياً',
                    'transaction_date' => $randomDate, // الحقل المطلوب في قاعدة البيانات
                    'created_at'       => $randomDate,
                    'updated_at'       => $randomDate,
                ]);
            }
        }

        $this->command->info('تم إنشاء الشركاء وسجل الحركات المالية بنجاح.');
    }
}