<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class PublicServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $filipino = app()->getLocale() === 'fil';

        return [
            'slug' => $this->slug,
            'name' => $filipino ? $this->name_fil : $this->name_en,
            'description' => $filipino ? $this->description_fil : $this->description_en,
            'eligibility' => $filipino ? $this->eligibility_fil : $this->eligibility_en,
            'fee_centavos' => $this->fee_centavos,
            'processing_time' => $filipino ? $this->processing_time_fil : $this->processing_time_en,
            'office_hours' => $filipino ? $this->office_hours_fil : $this->office_hours_en,
            'procedure_steps' => $filipino ? $this->procedure_steps_fil : $this->procedure_steps_en,
            'appointment_required' => $this->appointment_required,
            'contact' => [
                'email' => $this->contact_email,
                'phone' => $this->contact_phone,
            ],
            'requirements' => $this->whenLoaded('requirements', fn () => $this->requirements->map(fn ($requirement) => [
                'name' => $filipino ? $requirement->name_fil : $requirement->name_en,
                'details' => $filipino ? $requirement->details_fil : $requirement->details_en,
                'is_required' => $requirement->is_required,
            ])->values()),
        ];
    }
}
