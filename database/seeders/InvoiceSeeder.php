<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::first(); // ← جلب الطبيب المسجل
        
        if (!$doctor) {
            $this->command->warn('لم يتم العثور على طبيب.');
            return;
        }

        // ← فقط مرضى هذا الطبيب
        $patients = Patient::where('user_id', $doctor->id)->get();

        foreach ($patients as $patient) {
            $total = rand(100000, 2000000);
            $paid = rand(0, $total);
            $remaining = $total - $paid;

            Invoice::create([
                'patient_id'       => $patient->id,
                'user_id'          => $doctor->id, // ← ربط الفاتورة بالطبيب
                'total_amount'     => $total,
                'paid_amount'      => $paid,
                'remaining_amount' => $remaining,
                'discount'         => 0,
                'status'           => $this->determineStatus($paid, $total),
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