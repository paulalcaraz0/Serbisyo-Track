<?php

namespace Database\Factories;

use App\Enums\ServiceRequestStatus;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Support\BusinessDayCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'public_reference' => ServiceRequest::generateReference(),
            'tracking_pin_hash' => Hash::make('123456'),
            'status' => ServiceRequestStatus::Submitted,
            'locale' => 'en',
            'resident_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => null,
            'preferred_contact' => 'email',
            'general_location' => 'Fictional Zone 1',
            'request_details' => 'A fictional request created for automated testing.',
            'consented_at' => now(),
            'submitted_at' => now(),
            'due_at' => BusinessDayCalculator::add(now(), 3),
        ];
    }
}
