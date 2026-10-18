<?php

namespace App\Support;

/**
 * Deep link that opens the guardian mobile app on its Login screen (config `littlestep.guardian_app_login_url`).
 *
 * The link carries no token, password or email: only `verified=1`, which makes the app say the email was
 * verified. The guardian still signs in with their own password.
 */
class GuardianAppLink
{
    /**
     * Schemes the link may use: the app's own scheme, Expo Go during development (exp/exps),
     * and https for universal / app links. Anything else (javascript:, data:, http:) is refused.
     */
    private const ALLOWED_SCHEMES = ['littlestep', 'exp', 'exps', 'https'];

    /** The Login deep link marked as "just verified", or null when the configured link is missing or unsafe. */
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
