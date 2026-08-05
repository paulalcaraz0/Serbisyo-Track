<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class TrackServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reference' => strtoupper(trim((string) $this->input('reference'))),
            'pin' => preg_replace('/\s+/', '', (string) $this->input('pin')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'regex:/^ST-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}$/'],
            'pin' => ['required', 'digits:6'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reference.required' => __('phase3.validation.reference_required'),
            'reference.regex' => __('phase3.validation.reference_format'),
            'pin.required' => __('phase3.validation.pin_required'),
            'pin.digits' => __('phase3.validation.pin_format'),
        ];
    }
}
