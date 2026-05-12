<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentalChart extends Model
{
    protected $fillable = [
    'patient_id', 'procedure_id', 'tooth_number', 
    'doctor_notes', 'state', 'final_price', 'paid_now', 
    'currency', 'exchange_rate'
    ];

    // العلاقة التي تجلب بيانات العملية (الاسم، السلوج)
    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id');
    }

        // العلاقة مع المريض
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}