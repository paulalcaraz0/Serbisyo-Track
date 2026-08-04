<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class AdminServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name_en' => $this->name_en,
            'name_fil' => $this->name_fil,
            'description_en' => $this->description_en,
            'description_fil' => $this->description_fil,
            'eligibility_en' => $this->eligibility_en,
            'eligibility_fil' => $this->eligibility_fil,
            'fee_centavos' => $this->fee_centavos,
            'processing_time_en' => $this->processing_time_en,
            'processing_time_fil' => $this->processing_time_fil,
            'office_hours_en' => $this->office_hours_en,
            'office_hours_fil' => $this->office_hours_fil,
            'procedure_steps_en' => $this->procedure_steps_en,
            'procedure_steps_fil' => $this->procedure_steps_fil,
            'appointment_required' => $this->appointment_required,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'is_active' => $this->is_active,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'status' => $this->archived_at !== null ? 'archived' : ($this->is_active ? 'active' : 'inactive'),
            'requirements' => $this->whenLoaded('requirements', fn () => $this->requirements->map(fn ($requirement) => [
                'name_en' => $requirement->name_en,
                'name_fil' => $requirement->name_fil,
                'details_en' => $requirement->details_en,
                'details_fil' => $requirement->details_fil,
                'is_required' => $requirement->is_required,
            ])->values()),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
