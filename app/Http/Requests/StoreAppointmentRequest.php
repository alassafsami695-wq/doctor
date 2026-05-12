<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'patient_name'     => 'required|string|max:255', // نستقبل الاسم الآن
            'appointment_date' => 'required|date',
            'duration'         => 'nullable|integer|min:15',
            'status'           => 'nullable|string',
            'notes'            => 'nullable|string',
        ];
    }
}
