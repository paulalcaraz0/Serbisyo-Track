<?php

namespace App\Http\Requests\Admin;

use App\Enums\ServiceRequestStatus;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'service' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', ...array_column(ServiceRequestStatus::cases(), 'value')])],
        ];
    }

    /** @return array{date_from: string, date_to: string, service: string, status: string} */
    public function filters(): array
    {
        $data = $this->validated();
        $from = isset($data['date_from']) ? CarbonImmutable::parse((string) $data['date_from']) : CarbonImmutable::now()->subDays(29);
        $to = isset($data['date_to']) ? CarbonImmutable::parse((string) $data['date_to']) : CarbonImmutable::now();
        $service = isset($data['service']) && $data['service'] !== '' ? (string) $data['service'] : 'all';

        if ($from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['date_to' => 'Reports are limited to a 366-day range.']);
        }

        if ($service !== 'all' && ! Service::query()->where('slug', $service)->exists()) {
            throw ValidationException::withMessages(['service' => 'Choose a valid service.']);
        }

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'service' => $service,
            'status' => isset($data['status']) && $data['status'] !== '' ? (string) $data['status'] : 'all',
        ];
    }
}
