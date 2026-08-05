<?php

namespace App\Http\Resources;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ServiceRequest */
class StaffRequestSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->public_reference,
            'service' => [
                'slug' => $this->service->slug,
                'name' => $this->service->name_en,
            ],
            'status' => $this->status->value,
            'status_label' => __("phase3.statuses.{$this->status->value}.label"),
            'assignee' => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null,
            'submitted_at' => $this->submitted_at->toIso8601String(),
            'due_at' => $this->due_at?->toIso8601String(),
            'is_overdue' => ! $this->status->isTerminal() && $this->due_at?->isPast() === true,
            'has_appointment' => $this->appointment !== null,
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
