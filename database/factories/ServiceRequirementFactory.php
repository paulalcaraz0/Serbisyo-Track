<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequirement>
 */
class ServiceRequirementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'name_en' => 'Completed request form',
            'name_fil' => 'Kumpletong request form',
            'details_en' => null,
            'details_fil' => null,
            'is_required' => true,
            'sort_order' => 0,
        ];
    }
}
