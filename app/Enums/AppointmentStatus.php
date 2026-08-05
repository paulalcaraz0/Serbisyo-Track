<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Requested = 'requested';
    case Confirmed = 'confirmed';
    case RescheduleRequested = 'reschedule_requested';
    case Cancelled = 'cancelled';
}
