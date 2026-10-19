<?php

namespace App\Support;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The audit trail behind System Logs. Every entry records who did what to which record,
 * plus the IP address and device it came from. Entries are append-only (see Log).
 */
final class AuditLog
{
    public const CATEGORIES = [
        'security' => 'Sign-in & Security',
        'changes' => 'Changes',
        'access' => 'Views & Downloads',
    ];

    /** Known actions: label shown in the UI, category, and badge color for <x-badge>. */
    public const ACTIONS = [
        'login' => ['label' => 'Signed in', 'category' => 'security', 'color' => 'sky'],
        'logout' => ['label' => 'Signed out', 'category' => 'security', 'color' => 'sky'],
        'login_failed' => ['label' => 'Failed sign-in', 'category' => 'security', 'color' => 'red'],
        'login_denied' => ['label' => 'Sign-in blocked', 'category' => 'security', 'color' => 'red'],
        'registered' => ['label' => 'Registered', 'category' => 'security', 'color' => 'green'],
        'email_verified' => ['label' => 'Email verified', 'category' => 'security', 'color' => 'blue'],
        'password_changed' => ['label' => 'Password changed', 'category' => 'security', 'color' => 'blue'],
        'password_reset' => ['label' => 'Password reset', 'category' => 'security', 'color' => 'blue'],
        'created' => ['label' => 'Created', 'category' => 'changes', 'color' => 'green'],
        'saved' => ['label' => 'Saved', 'category' => 'changes', 'color' => 'green'],
        'updated' => ['label' => 'Updated', 'category' => 'changes', 'color' => 'blue'],
        'deleted' => ['label' => 'Archived', 'category' => 'changes', 'color' => 'red'],
        'restored' => ['label' => 'Restored', 'category' => 'changes', 'color' => 'amber'],
        'verification_resent' => ['label' => 'Verification resent', 'category' => 'changes', 'color' => 'blue'],
        'seeded_sample_evaluation' => ['label' => 'Sample data', 'category' => 'changes', 'color' => 'gray'],
        'viewed' => ['label' => 'Viewed', 'category' => 'access', 'color' => 'gray'],
        'downloaded' => ['label' => 'Downloaded', 'category' => 'access', 'color' => 'violet'],
        'exported' => ['label' => 'Exported', 'category' => 'access', 'color' => 'violet'],
    ];

    public const ENTITY_LABELS = [
        'User' => 'Account',
        'Teacher' => 'Teacher profile',
        'Guardian' => 'Guardian',
        'Child' => 'Child',
        'ProgressRecord' => 'Progress record',
        'ChecklistItem' => 'Checklist item',
        'Log' => 'System logs',
    ];

    public static function record(string $action, string $entityType, ?int $entityId = null, ?string $details = null, ?int $userId = null): Log
    {
        $request = app()->runningInConsole() && ! app()->runningUnitTests() ? null : request();

        return Log::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            // 0 was used as "no record" by older calls; store it as empty.
            'entity_id' => $entityId ?: null,
            'details' => $details,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? (Str::limit((string) $request->userAgent(), 252) ?: null) : null,
        ]);
    }

    public static function actionLabel(string $action): string
    {
        return self::ACTIONS[$action]['label'] ?? Str::ucfirst(str_replace('_', ' ', $action));
    }

    public static function actionColor(string $action): string
    {
        return self::ACTIONS[$action]['color'] ?? 'gray';
    }

    public static function actionCategory(string $action): ?string
    {
        return self::ACTIONS[$action]['category'] ?? null;
    }

    /** @return list<string> */
    public static function actionsIn(string $category): array
    {
        return array_keys(array_filter(self::ACTIONS, fn (array $meta) => $meta['category'] === $category));
    }

    public static function entityLabel(?string $entityType): string
    {
        if ($entityType === null || $entityType === '') {
            return '—';
        }

        return self::ENTITY_LABELS[$entityType] ?? Str::headline($entityType);
    }

    /** A short "Browser on OS" description of a user agent, e.g. "Chrome on Windows" or "LittleStep app on Android". */
    public static function describeDevice(?string $userAgent): string
    {
        if (blank($userAgent)) {
            return 'Unknown device';
        }

        $browser = match (true) {
            str_contains($userAgent, 'Expo') || str_contains($userAgent, 'okhttp') || str_contains($userAgent, 'CFNetwork') => 'LittleStep app',
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') || str_contains($userAgent, 'CriOS/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Browser',
        };
        $platform = match (true) {
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || (str_contains($userAgent, 'Darwin') && $browser === 'LittleStep app') => 'iOS',
            str_contains($userAgent, 'Android') || str_contains($userAgent, 'okhttp') => 'Android',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS X') || str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => null,
        };

        return $platform ? "{$browser} on {$platform}" : $browser;
    }
}
