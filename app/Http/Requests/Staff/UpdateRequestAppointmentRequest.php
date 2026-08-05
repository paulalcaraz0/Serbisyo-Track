<?php

namespace App\Http\Requests\Staff;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequestAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::RescheduleRequested->value,
                AppointmentStatus::Cancelled->value,
            ])],
            'confirmed_start_at' => [
                'nullable',
                'required_if:status,'.AppointmentStatus::Confirmed->value,
                'date',
                'after:now',
                'before_or_equal:'.now()->addYear()->toDateTimeString(),
            ],
            'private_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
