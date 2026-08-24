<?php

namespace App\Services;

use App\Enums\AuditEventType;
use App\Models\AuditEvent;
use App\Models\User;
use InvalidArgumentException;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ?User $actor,
        AuditEventType $action,
        string $subjectType,
        string|int|null $subjectIdentifier = null,
        array $metadata = [],
    ): AuditEvent {
        if (! in_array($subjectType, ['staff', 'service', 'request', 'office_settings', 'report', 'retention', 'holiday', 'announcement'], true)) {
            throw new InvalidArgumentException('Unsupported audit subject type.');
        }

        $allowedKeys = $this->allowedMetadataKeys($action);
        $sanitized = [];

        foreach ($metadata as $key => $value) {
            if (in_array($key, $allowedKeys, true) && (is_scalar($value) || $value === null)) {
                $sanitized[$key] = $value;
            }
        }

        return AuditEvent::query()->create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_identifier' => $subjectIdentifier === null ? null : mb_substr((string) $subjectIdentifier, 0, 64),
            'metadata' => $sanitized === [] ? null : $sanitized,
        ]);
    }

    /** @return array<int, string> */
    private function allowedMetadataKeys(AuditEventType $action): array
    {
        return match ($action) {
            AuditEventType::StaffCreated,
            AuditEventType::StaffUpdated => ['staff_id', 'role', 'is_active', 'assignments_released'],
            AuditEventType::ServiceCreated,
            AuditEventType::ServiceUpdated,
            AuditEventType::ServiceArchived,
            AuditEventType::ServiceRestored => ['service_slug'],
            AuditEventType::RequestAssigned => ['request_reference', 'assignee_id'],
            AuditEventType::RequestStatusChanged => ['request_reference', 'from_status', 'to_status'],
            AuditEventType::RequestInternalNoteAdded => ['request_reference'],
            AuditEventType::RequestAppointmentUpdated => ['request_reference', 'appointment_status'],
            AuditEventType::OfficeSettingsUpdated => ['retention_days'],
            AuditEventType::ReportExported => ['date_from', 'date_to', 'service_slug', 'status', 'row_count'],
            AuditEventType::RetentionPurged => ['cutoff_date', 'request_count', 'attachment_count', 'dry_run'],
            AuditEventType::HolidayCreated,
            AuditEventType::HolidayUpdated,
            AuditEventType::HolidayDeleted => ['holiday_date', 'is_recurring'],
            AuditEventType::AnnouncementCreated,
            AuditEventType::AnnouncementUpdated,
            AuditEventType::AnnouncementDeleted => ['announcement_level'],
        };
    }
}
