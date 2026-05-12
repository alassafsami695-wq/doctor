<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = ['patient_id', 'user_id', 'appointment_date', 'duration', 'status', 'notes'];

    public function patient(): BelongsTo {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dentalCharts() {
        return $this->hasMany(DentalChart::class);
    }
}