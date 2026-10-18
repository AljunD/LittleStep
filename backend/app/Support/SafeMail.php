<?php

namespace App\Support;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SafeMail
{
    public const VERIFICATION_FAILED = 'The verification email could not be sent because the mail server could not be reached. Check the internet connection and mail settings, then use "Resend Verification Email" from the guardian\'s ⋮ menu.';

    public static function attempt(callable $send): bool
    {
        try {
            $send();

            return true;
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return false;
        }
    }
}
