<?php

namespace Database\Factories;

use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Models\RequestActivity;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequestActivity>
 */
class RequestActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'actor_id' => null,
            'subject_user_id' => null,
            'event_type' => RequestActivityType::Submitted,
            'from_status' => null,
            'to_status' => ServiceRequestStatus::Submitted,
            'public_message_en' => 'The request was received and is waiting for staff review.',
            'public_message_fil' => 'Natanggap ang request at naghihintay ng pagsusuri ng staff.',
            'private_details' => null,
            'created_at' => now(),
        ];
    }
}
