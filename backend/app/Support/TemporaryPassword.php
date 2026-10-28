<?php

namespace App\Support;

/**
 * Passwords a teacher hands to a guardian, such as "k7mq-p2rw-z9tx": random for every account, easy to
 * read out or copy from paper, and flagged so the app asks the guardian to change it (`must_change_password`).
 */
final class TemporaryPassword
{
    // No i, l, o, 0 or 1, which are easy to mix up.
    private const CHARACTERS = 'abcdefghjkmnpqrstuvwxyz23456789';

    public static function generate(): string
    {
        $groups = [];
        for ($group = 0; $group < 3; $group++) {
            $characters = '';
            for ($character = 0; $character < 4; $character++) {
                $characters .= self::CHARACTERS[random_int(0, strlen(self::CHARACTERS) - 1)];
            }
            $groups[] = $characters;
        }

        return implode('-', $groups);
    }
}
