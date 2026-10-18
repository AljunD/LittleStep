<?php

namespace App\Support;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Sends mail without letting an unreachable mail server (no internet, wrong SMTP settings) turn a
 * finished action into a 500 error. The failure is written to the log, and the caller tells the user.
 */
class SafeMail
{
    /** Message for the teacher when a verification email could not be sent. */
    public const VERIFICATION_FAILED = 'The verification email could not be sent because the mail server could not be reached. Check the internet connection and mail settings, then use "Resend Verification Email" from the guardian\'s ⋮ menu.';

    /** Runs `$send` and returns false (after logging the error) if the mail server could not be reached. */
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
