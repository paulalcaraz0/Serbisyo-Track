<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Notifications\ResidentRequestUpdated;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;

class ResidentUpdateNotifier
{
    public function send(ServiceRequest $serviceRequest, string $publicMessageEn, string $publicMessageFil): void
    {
        if ($serviceRequest->preferred_contact !== 'email' || $serviceRequest->contact_email === null) {
            return;
        }

        $serviceRequest->loadMissing('service');
        $locale = $serviceRequest->locale === 'fil' ? 'fil' : 'en';
        $status = $serviceRequest->status->value;
        $serviceName = $locale === 'fil' ? $serviceRequest->service->name_fil : $serviceRequest->service->name_en;
        $message = $locale === 'fil' ? $publicMessageFil : $publicMessageEn;

        Notification::route('mail', $serviceRequest->contact_email)
            ->notify(new ResidentRequestUpdated(
                reference: $serviceRequest->public_reference,
                serviceName: $serviceName,
                statusLabel: Lang::get("phase3.statuses.{$status}.label", [], $locale),
                publicMessage: $message,
                requestLocale: $locale,
            ));
    }
}
