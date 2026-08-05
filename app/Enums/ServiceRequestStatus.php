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

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Rejected, self::Cancelled], true);
    }
}
