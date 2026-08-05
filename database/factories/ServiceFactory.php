<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'slug' => Str::slug($name),
            'name_en' => Str::title($name),
            'name_fil' => 'Serbisyo '.Str::title($name),
            'description_en' => fake()->sentence(),
            'description_fil' => 'Kathang-isip na paglalarawan ng serbisyo.',
            'eligibility_en' => 'Residents of fictional Barangay Haraya.',
            'eligibility_fil' => 'Mga residente ng kathang-isip na Barangay Haraya.',
            'fee_centavos' => 0,
            'processing_time_en' => '1 to 3 business days',
            'processing_time_fil' => '1 hanggang 3 araw ng trabaho',
            'office_hours_en' => 'Monday to Friday, 8:00 AM to 5:00 PM',
            'office_hours_fil' => 'Lunes hanggang Biyernes, 8:00 AM hanggang 5:00 PM',
            'procedure_steps_en' => ['Review the requirements.', 'Submit a request.'],
            'procedure_steps_fil' => ['Suriin ang requirements.', 'Magsumite ng kahilingan.'],
            'appointment_required' => false,
            'contact_email' => 'help@serbisyotrack.test',
            'contact_phone' => '(02) 8000 2026',
            'is_active' => true,
            'target_business_days' => 3,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['is_active' => false, 'archived_at' => now()]);
    }
}
