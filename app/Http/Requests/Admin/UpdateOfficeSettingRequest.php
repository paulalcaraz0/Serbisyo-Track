<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'office_name_en' => ['required', 'string', 'max:150'],
            'office_name_fil' => ['required', 'string', 'max:150'],
            'contact_email' => ['required', 'email', 'max:150'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'address_en' => ['required', 'string', 'max:255'],
            'address_fil' => ['required', 'string', 'max:255'],
            'retention_days' => ['required', 'integer', 'min:30', 'max:3650'],
        ];
    }
}
