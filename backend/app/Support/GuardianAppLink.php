<?php

namespace App\Support;

class GuardianAppLink
{
    private const ALLOWED_SCHEMES = ['littlestep', 'exp', 'exps', 'https'];

    public static function verifiedLoginUrl(): ?string
    {
        $url = trim((string) config('littlestep.guardian_app_login_url'));
        $parts = parse_url($url);

        if (
            $url === ''
            || $parts === false
            || ! in_array(strtolower($parts['scheme'] ?? ''), self::ALLOWED_SCHEMES, true)
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['fragment'])
        ) {
            return null;
        }

        return $url.(isset($parts['query']) ? '&' : '?').'verified=1';
    }
}
