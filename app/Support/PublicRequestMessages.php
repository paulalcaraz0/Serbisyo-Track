<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Enums\ServiceRequestStatus;
use Illuminate\Support\Facades\Lang;

class PublicRequestMessages
{
    /** @return array{en: string, fil: string} */
    public static function status(ServiceRequestStatus $status): array
    {
        $key = "phase3.statuses.{$status->value}.description";

        return [
            'en' => Lang::get($key, [], 'en'),
            'fil' => Lang::get($key, [], 'fil'),
        ];
    }

    /** @return array{en: string, fil: string} */
    public static function appointment(AppointmentStatus $status): array
    {
        $key = "phase4.appointment_updates.{$status->value}";

        return [
            'en' => Lang::get($key, [], 'en'),
            'fil' => Lang::get($key, [], 'fil'),
        ];
    }
}
