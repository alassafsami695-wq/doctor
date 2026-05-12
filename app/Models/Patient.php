<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
    'full_name', 'phone', 'national_id', 'birth_date', 
    'gender', 'blood_type', 'medical_history', 'allergies',
    'panorama_image', 'current_medications'
    ];
        // ميزة تجارية: معرفة المديونية الإجمالية للمريض فوراً
        public function totalDebt() {
            return $this->invoices()->sum('remaining_amount');
        }

        public function labOrders(): HasMany {
            return $this->hasMany(LabOrder::class);
        }
    public function appointments() {
        return $this->hasMany(Appointment::class);
    }

    public function dentalHistory()
{
    // الربط مع سجلات الأسنان وجلب الإجراء المرتبط بكل سجل تلقائياً
    return $this->hasMany(DentalChart::class)->with('procedure');
}

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

    public function files() {
        return $this->hasMany(PatientFile::class);
    }

    public function prescriptions() {
        return $this->hasMany(Prescription::class);
    }
}