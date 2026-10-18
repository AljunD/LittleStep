<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the custom verification email message.
     *
     * @param  string  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Verify Your LittleStep Account')
            ->view('emails.verify-email', [
                'verificationUrl' => $url,
            ]);
    }

    /**
     * Generate the verification URL using the correct route.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        // The link is opened on another device (usually the guardian's phone), so it is built from APP_URL
        // instead of whatever address the teacher's browser used (e.g. localhost, which a phone can't reach).
        URL::forceRootUrl(rtrim((string) config('app.url'), '/'));

        try {
            return URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        } finally {
            URL::forceRootUrl(null);
        }
    }
}
