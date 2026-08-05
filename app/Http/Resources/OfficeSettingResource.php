<?php

namespace App\Http\Resources;

use App\Models\OfficeSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OfficeSetting */
class OfficeSettingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'office_name_en' => $this->office_name_en,
            'office_name_fil' => $this->office_name_fil,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'address_en' => $this->address_en,
            'address_fil' => $this->address_fil,
            'retention_days' => $this->retention_days,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
