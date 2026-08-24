<?php

namespace App\Enums;

enum AuditEventType: string
{
    case StaffCreated = 'staff.created';
    case StaffUpdated = 'staff.updated';
    case ServiceCreated = 'service.created';
    case ServiceUpdated = 'service.updated';
    case ServiceArchived = 'service.archived';
    case ServiceRestored = 'service.restored';
    case RequestAssigned = 'request.assigned';
    case RequestStatusChanged = 'request.status_changed';
    case RequestInternalNoteAdded = 'request.internal_note_added';
    case RequestAppointmentUpdated = 'request.appointment_updated';
    case OfficeSettingsUpdated = 'office_settings.updated';
    case ReportExported = 'report.exported';
    case RetentionPurged = 'retention.purged';
    case HolidayCreated = 'holiday.created';
    case HolidayUpdated = 'holiday.updated';
    case HolidayDeleted = 'holiday.deleted';
    case AnnouncementCreated = 'announcement.created';
    case AnnouncementUpdated = 'announcement.updated';
    case AnnouncementDeleted = 'announcement.deleted';

    public function label(): string
    {
        return match ($this) {
            self::StaffCreated => 'Staff account created',
            self::StaffUpdated => 'Staff account updated',
            self::ServiceCreated => 'Service created',
            self::ServiceUpdated => 'Service updated',
            self::ServiceArchived => 'Service archived',
            self::ServiceRestored => 'Service restored',
            self::RequestAssigned => 'Request assignment changed',
            self::RequestStatusChanged => 'Request status changed',
            self::RequestInternalNoteAdded => 'Internal note added',
            self::RequestAppointmentUpdated => 'Appointment updated',
            self::OfficeSettingsUpdated => 'Office settings updated',
            self::ReportExported => 'Request report exported',
            self::RetentionPurged => 'Retention cleanup executed',
            self::HolidayCreated => 'Holiday created',
            self::HolidayUpdated => 'Holiday updated',
            self::HolidayDeleted => 'Holiday deleted',
            self::AnnouncementCreated => 'Announcement created',
            self::AnnouncementUpdated => 'Announcement updated',
            self::AnnouncementDeleted => 'Announcement deleted',
        };
    }
}
