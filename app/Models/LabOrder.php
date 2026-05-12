<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    protected $fillable = [
        'patient_id', 'lab_name', 'work_type', 
        'tooth_number', 'cost', 'sent_date', 
        'expected_date', 'status'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}