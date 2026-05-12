<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientFile extends Model
{
    protected $fillable = ['patient_id', 'file_path', 'file_type', 'description'];

    public function patient() {
        return $this->belongsTo(Patient::class);
    }
}