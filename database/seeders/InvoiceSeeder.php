<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();

        foreach ($patients as $patient) {
            $total = rand(100000, 2000000);
            $paid = rand(0, $total);
            $remaining = $total - $paid;

            Invoice::create([
                'patient_id'       => $patient->id,
                'total_amount'     => $total,
                'paid_amount'      => $paid,
                'remaining_amount' => $remaining,
                'discount'         => 0,
                // تحديث الحالات لتطابق الـ Enum في المهاجرة
                'status'           => $this->determineStatus($paid, $total),
                // تم حذف 'description' لأنه غير موجود في الجدول
            ]);
        }
    }

    private function determineStatus($paid, $total)
    {
        if ($paid <= 0) return 'unpaid';
        if ($paid < $total) return 'partially_paid';
        return 'paid';
    }
}