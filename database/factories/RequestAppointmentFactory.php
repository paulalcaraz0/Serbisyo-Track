<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\RequestAppointment;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequestAppointment>
 */
class RequestAppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'preferred_date' => now()->addWeek()->toDateString(),
            'preferred_time_window' => 'morning',
            'resident_note' => null,
            'status' => AppointmentStatus::Requested,
            'confirmed_start_at' => null,
        ];
    }
}
