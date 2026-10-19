<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    public function __construct(private readonly CarbonInterface $changedAt) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Built from APP_URL, not the request host, so the link always points at the real portal.
        $resetUrl = rtrim((string) config('app.url'), '/').route('auth.password.request', [], false);

        return (new MailMessage)
            ->subject('Your LittleStep Password Was Changed')
            ->view('emails.password-changed', [
                'changedAt' => $this->changedAt->copy()->timezone(config('app.timezone'))->format('F d, Y \a\t h:i A T'),
                'resetUrl' => $resetUrl,
            ]);
    }
}
