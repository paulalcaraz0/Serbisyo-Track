<?php

namespace App\Http\Requests\Public;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'service_slug' => strtolower(trim((string) $this->input('service_slug'))),
            'resident_name' => trim((string) $this->input('resident_name')),
            'contact_email' => $this->filled('contact_email') ? trim((string) $this->input('contact_email')) : null,
            'contact_phone' => $this->filled('contact_phone') ? trim((string) $this->input('contact_phone')) : null,
            'general_location' => $this->filled('general_location') ? trim((string) $this->input('general_location')) : null,
            'request_details' => trim((string) $this->input('request_details')),
            'appointment_note' => $this->filled('appointment_note') ? trim((string) $this->input('appointment_note')) : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'service_slug' => [
                'required',
                'string',
                Rule::exists('services', 'slug')->where(fn ($query) => $query->where('is_active', true)->whereNull('archived_at')),
            ],
            'resident_name' => ['required', 'string', 'min:2', 'max:100'],
            'contact_email' => ['nullable', 'email:rfc', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'regex:/^[0-9+().\-\s]{7,30}$/'],
            'preferred_contact' => ['required', Rule::in(['email', 'phone'])],
            'general_location' => ['nullable', 'string', 'max:255'],
            'request_details' => ['required', 'string', 'min:20', 'max:2000'],
            'appointment_requested' => ['required', 'boolean'],
            'appointment_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tomorrow', 'before_or_equal:'.now()->addDays(90)->toDateString()],
            'appointment_time_window' => ['nullable', Rule::in(['morning', 'afternoon'])],
            'appointment_note' => ['nullable', 'string', 'max:500'],
            'attachments' => ['nullable', 'array', 'max:'.config('serbisyo.attachment_max_files', 5)],
            'attachments.*' => ['file', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max((int) config('serbisyo.attachment_max_kilobytes', 5120))],
            'privacy_consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('service_slug')) {
                return;
            }

            $service = Service::query()->where('slug', $this->string('service_slug'))->first();
            $appointmentRequested = $service?->appointment_required || $this->boolean('appointment_requested');

            if ($this->string('preferred_contact')->toString() === 'email' && ! $this->filled('contact_email')) {
                $validator->errors()->add('contact_email', __('phase3.validation.email_for_contact'));
            }

            if ($this->string('preferred_contact')->toString() === 'phone' && ! $this->filled('contact_phone')) {
                $validator->errors()->add('contact_phone', __('phase3.validation.phone_for_contact'));
            }

            if ($appointmentRequested && ! $this->filled('appointment_date')) {
                $validator->errors()->add('appointment_date', __('phase3.validation.appointment_date_required'));
            }

            if ($appointmentRequested && ! $this->filled('appointment_time_window')) {
                $validator->errors()->add('appointment_time_window', __('phase3.validation.appointment_time_required'));
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'service_slug.exists' => __('phase3.validation.service_unavailable'),
            'resident_name.required' => __('phase3.validation.name_required'),
            'contact_email.email' => __('phase3.validation.email_invalid'),
            'contact_phone.regex' => __('phase3.validation.phone_invalid'),
            'request_details.required' => __('phase3.validation.details_required'),
            'request_details.min' => __('phase3.validation.details_min'),
            'privacy_consent.accepted' => __('phase3.validation.consent_required'),
            'attachments.max' => __('phase3.validation.too_many_files'),
            'attachments.*.max' => __('phase3.validation.file_too_large'),
            'attachments.*.mimes' => __('phase3.validation.file_type'),
        ];
    }
}
