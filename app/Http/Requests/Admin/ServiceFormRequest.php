<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class ServiceFormRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:150'],
            'name_fil' => ['required', 'string', 'max:150'],
            'description_en' => ['required', 'string', 'max:3000'],
            'description_fil' => ['required', 'string', 'max:3000'],
            'eligibility_en' => ['required', 'string', 'max:2000'],
            'eligibility_fil' => ['required', 'string', 'max:2000'],
            'fee_centavos' => ['required', 'integer', 'min:0', 'max:10000000'],
            'processing_time_en' => ['required', 'string', 'max:255'],
            'processing_time_fil' => ['required', 'string', 'max:255'],
            'office_hours_en' => ['required', 'string', 'max:255'],
            'office_hours_fil' => ['required', 'string', 'max:255'],
            'procedure_steps_en' => ['required', 'array', 'min:1', 'max:20'],
            'procedure_steps_en.*' => ['required', 'string', 'max:1000'],
            'procedure_steps_fil' => ['required', 'array', 'min:1', 'max:20'],
            'procedure_steps_fil.*' => ['required', 'string', 'max:1000'],
            'appointment_required' => ['required', 'boolean'],
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
            'requirements' => ['required', 'array', 'min:1', 'max:30'],
            'requirements.*.name_en' => ['required', 'string', 'max:255'],
            'requirements.*.name_fil' => ['required', 'string', 'max:255'],
            'requirements.*.details_en' => ['nullable', 'string', 'max:1000'],
            'requirements.*.details_fil' => ['nullable', 'string', 'max:1000'],
            'requirements.*.is_required' => ['required', 'boolean'],
        ];
    }
}
