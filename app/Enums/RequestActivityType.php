<?php

namespace App\Enums;

enum RequestActivityType: string
{
    case Submitted = 'submitted';
    case Assignment = 'assignment';
    case StatusChange = 'status_change';
    case InternalNote = 'internal_note';
    case Appointment = 'appointment';
}
