<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; } // تأكد من جعلها true

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|min:3|max:100',
            'phone' => 'required|string|unique:patients,phone',
            'gender' => 'required|in:male,female',
            'birth_date' => 'nullable|date',
        ];
    }
}
