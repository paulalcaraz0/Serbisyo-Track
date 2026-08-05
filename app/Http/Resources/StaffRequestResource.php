<?php

namespace App\Http\Resources;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ServiceRequest */
class StaffRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->public_reference,
            'service' => [
                'slug' => $this->service->slug,
                'name' => $this->service->name_en,
                'processing_time' => $this->service->processing_time_en,
                'target_business_days' => $this->service->target_business_days,
            ],
            'status' => $this->status->value,
            'status_label' => __("phase3.statuses.{$this->status->value}.label"),
            'resident' => [
                'name' => $this->resident_name,
                'email' => $this->contact_email,
                'phone' => $this->contact_phone,
                'preferred_contact' => $this->preferred_contact,
                'general_location' => $this->general_location,
            ],
            'request_details' => $this->request_details,
            'consented_at' => $this->consented_at->toIso8601String(),
            'submitted_at' => $this->submitted_at->toIso8601String(),
            'due_at' => $this->due_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'is_overdue' => ! $this->status->isTerminal() && $this->due_at?->isPast() === true,
            'assignee' => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null,
            'appointment' => $this->appointment ? [
                'preferred_date' => $this->appointment->preferred_date->toDateString(),
                'preferred_time_window' => $this->appointment->preferred_time_window,
                'resident_note' => $this->appointment->resident_note,
                'status' => $this->appointment->status->value,
                'confirmed_start_at' => $this->appointment->confirmed_start_at?->toIso8601String(),
            ] : null,
            'attachments' => $this->attachments->map(fn ($attachment) => [
                'public_id' => $attachment->public_id,
                'name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size_bytes' => $attachment->size_bytes,
            ])->values(),
            'activities' => $this->activities->map(fn ($activity) => [
                'id' => $activity->id,
                'event_type' => $activity->event_type->value,
                'actor' => $activity->actor?->name,
                'subject_user' => $activity->subjectUser?->name,
                'from_status' => $activity->from_status?->value,
                'to_status' => $activity->to_status?->value,
                'public_message_en' => $activity->public_message_en,
                'public_message_fil' => $activity->public_message_fil,
                'private_details' => $activity->private_details,
                'created_at' => $activity->created_at->toIso8601String(),
            ])->values(),
            'permissions' => [
                'assign' => $request->user()?->can('assign', $this->resource) ?? false,
                'transition' => $request->user()?->can('transition', $this->resource) ?? false,
                'add_note' => $request->user()?->can('addInternalNote', $this->resource) ?? false,
                'manage_appointment' => $request->user()?->can('manageAppointment', $this->resource) ?? false,
            ],
        ];
    }
}
