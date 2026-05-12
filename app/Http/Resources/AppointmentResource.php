<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id, // السطر الأهم لحل مشكلة undefined
            'appointment_date' => $this->appointment_date,
            'duration' => $this->duration . ' دقيقة',
            'status' => $this->status,
            'notes' => $this->notes ?? 'لا توجد ملاحظات',
            'patient' => [
                'id' => $this->patient->id ?? null,
                'full_name' => $this->patient->full_name ?? 'مريض غير معروف',
            ],
            'doctor' => [
                'id' => $this->doctor->id ?? null,
                'name' => $this->doctor->name ?? 'غير محدد',
            ],
        ];
    }
}