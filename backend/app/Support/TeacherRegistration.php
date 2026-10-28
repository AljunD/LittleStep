<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Who may create a teacher account at /auth/register. Teachers enter the code from TEACHER_REGISTRATION_CODE;
 * without a code, registration stays open on a local or test install and is closed in production.
 */
final class TeacherRegistration
{
    public static function code(): ?string
    {
        $code = trim((string) config('littlestep.teacher_registration_code'));

        return $code === '' ? null : $code;
    }

    public static function isOpen(): bool
    {
        return self::code() !== null || ! app()->isProduction();
    }

    /** Spaces around the code and letter case are ignored, so a code read out or copied from a message still works. */
    public static function accepts(mixed $code): bool
    {
        $expected = self::code();
        if ($expected === null) {
            return self::isOpen();
        }

        return is_string($code) && hash_equals(Str::lower($expected), Str::lower(trim($code)));
    }
}
