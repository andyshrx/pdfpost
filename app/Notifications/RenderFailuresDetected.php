<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenderFailuresDetected extends Notification
{
    use Queueable;

    public function __construct(public ?string $lastError = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('PDFPost: renders are failing')
            ->line('The last three renders have all failed.')
            ->line('Most recent error: '.($this->lastError ?? 'unknown'))
            ->line('Check that Gotenberg is running and reachable, then look at the render history for details.');
    }
}
