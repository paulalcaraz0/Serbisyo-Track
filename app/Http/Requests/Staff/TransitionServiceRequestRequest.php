<?php

namespace App\Http\Requests\Staff;

use App\Enums\ServiceRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class TransitionServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(ServiceRequestStatus::class)],
            'public_message_en' => ['nullable', 'string', 'max:500', 'required_with:public_message_fil'],
            'public_message_fil' => ['nullable', 'string', 'max:500', 'required_with:public_message_en'],
            'private_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $status = $this->string('status')->toString();

            if (in_array($status, [ServiceRequestStatus::NeedsInformation->value, ServiceRequestStatus::Rejected->value], true)) {
                if (! $this->filled('public_message_en')) {
                    $validator->errors()->add('public_message_en', 'Add clear English guidance for the resident.');
                }

                if (! $this->filled('public_message_fil')) {
                    $validator->errors()->add('public_message_fil', 'Add clear Filipino guidance for the resident.');
                }
            }
        });
    }
}
