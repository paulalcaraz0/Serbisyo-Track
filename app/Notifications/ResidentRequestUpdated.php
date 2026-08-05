<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResidentRequestUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $reference,
        public readonly string $serviceName,
        public readonly string $statusLabel,
        public readonly string $publicMessage,
        public readonly string $requestLocale,
    ) {
        $this->afterCommit();
        $this->onQueue('notifications');
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $replace = fn (string $key, array $values = []): string => Lang::get($key, $values, $this->requestLocale);

        return (new MailMessage)
            ->subject($replace('phase4.email.subject', ['reference' => $this->reference]))
            ->greeting($replace('phase4.email.greeting'))
            ->line($replace('phase4.email.intro'))
            ->line($replace('phase4.email.service', ['service' => $this->serviceName]))
            ->line($replace('phase4.email.status', ['status' => $this->statusLabel]))
            ->line($this->publicMessage)
            ->line($replace('phase4.email.reference', ['reference' => $this->reference]))
            ->action($replace('phase4.email.action'), route('tracking.index'))
            ->line($replace('phase4.email.security'))
            ->line($replace('phase4.email.demo'));
    }
}
