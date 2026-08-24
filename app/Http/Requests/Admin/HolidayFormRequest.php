<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HolidayFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', Rule::unique('holidays', 'date')->ignore($this->route('holiday'))],
            'name_en' => ['required', 'string', 'max:150'],
            'name_fil' => ['required', 'string', 'max:150'],
            'is_recurring' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.unique' => 'A holiday already exists on this date.',
        ];
    }
}
