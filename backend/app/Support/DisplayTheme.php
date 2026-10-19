<?php

namespace App\Support;

use Illuminate\Http\Request;

class DisplayTheme
{
    public const COOKIE = 'littlestep_theme';

    public const DEFAULT = 'light';

    public const OPTIONS = [
        'light' => 'Light',
        'dark' => 'Dark',
        'system' => 'System',
    ];

    /** The teacher's saved theme for this browser, or the default when none or an unknown value is stored. */
    public static function current(?Request $request = null): string
    {
        $theme = ($request ?? request())->cookie(self::COOKIE);

        return is_string($theme) && isset(self::OPTIONS[$theme]) ? $theme : self::DEFAULT;
    }
}
