<?php

namespace App\Enums;

enum ServiceRequestStatus: string
{
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';
    case NeedsInformation = 'needs_information';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case ReadyForRelease = 'ready_for_release';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Acknowledged => 'Acknowledged',
            self::NeedsInformation => 'Needs information',
            self::Scheduled => 'Scheduled',
            self::InProgress => 'In progress',
            self::ReadyForRelease => 'Ready for release',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Rejected, self::Cancelled], true);
    }
}
