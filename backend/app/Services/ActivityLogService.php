<?php

namespace App\Services;

use App\Models\EccdChecklistItem;
use App\Models\Log;
use App\Models\ProgressRecord;
use App\Models\User;
use App\Support\AuditLog;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reads the audit trail for System Logs. Every query is limited to the signed-in user's own
 * entries; there is no way to read another user's logs through this service.
 */
class ActivityLogService
{
    public const PER_PAGE = 20;

    public const SUMMARY_DAYS = 30;

    public const FAILED_SIGN_IN_ALERT_DAYS = 7;

    /** Characters that make spreadsheet apps treat a CSV cell as a formula. */
    private const CSV_FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    public function ownLogs(User $user): Builder
    {
        return Log::query()->where('user_id', $user->id);
    }

    public function filteredQuery(User $user, array $filters): Builder
    {
        $query = $this->ownLogs($user)
            ->when($filters['category'] ?? null, fn (Builder $q, string $category) => $q->whereIn('action', AuditLog::actionsIn($category)))
            ->when($filters['action'] ?? null, fn (Builder $q, string $action) => $q->where('action', $action))
            ->when($filters['entity_type'] ?? null, fn (Builder $q, string $type) => $q->where('entity_type', $type))
            ->when($filters['from_date'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));

        return Search::apply($query, $filters['search'] ?? null, ['details'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /** Counts for the last SUMMARY_DAYS days, the recent failed sign-ins, and the previous sign-in. */
    public function summary(User $user): array
    {
        $countsByAction = $this->ownLogs($user)
            ->where('created_at', '>=', now()->subDays(self::SUMMARY_DAYS))
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->pluck('total', 'action');
        $countIn = fn (string $category) => $countsByAction->only(AuditLog::actionsIn($category))->sum();

        return [
            'sign_ins' => (int) ($countsByAction['login'] ?? 0),
            'failed_sign_ins' => (int) ($countsByAction['login_failed'] ?? 0) + (int) ($countsByAction['login_denied'] ?? 0),
            'changes' => (int) $countIn('changes'),
            'access' => (int) $countIn('access'),
            'recent_failed_sign_ins' => $this->ownLogs($user)
                ->whereIn('action', ['login_failed', 'login_denied'])
                ->where('created_at', '>=', now()->subDays(self::FAILED_SIGN_IN_ALERT_DAYS))
                ->count(),
            // The newest sign-in is the current one, so "previous" is the one before it.
            'previous_sign_in' => $this->ownLogs($user)->where('action', 'login')
                ->orderByDesc('created_at')->orderByDesc('id')->skip(1)->first(),
        ];
    }

    /** Distinct values of a column in the user's own logs, for filter dropdowns. */
    public function ownDistinct(User $user, string $column): Collection
    {
        return $this->ownLogs($user)->whereNotNull($column)->distinct()->orderBy($column)->pluck($column);
    }

    /**
     * Links to the records behind log entries, keyed "Type:id". Only records the teacher still
     * owns and that are not archived get a link; everything else is shown as plain text.
     */
    public function recordLinks(User $user, iterable $logs): array
    {
        $teacher = $user->teacher;
        $idsByType = collect($logs)
            ->filter(fn (Log $log) => $log->entity_id !== null)
            ->groupBy('entity_type')
            ->map(fn (Collection $group) => $group->pluck('entity_id')->unique()->values()->all());

        if (! $teacher || $idsByType->isEmpty()) {
            return [];
        }

        $resolvers = [
            'Child' => fn (array $ids) => $teacher->ownedChildren()->whereKey($ids)->pluck('children.id')
                ->mapWithKeys(fn ($id) => [$id => route('children.show', $id)]),
            'Guardian' => fn (array $ids) => $teacher->ownedGuardians()->whereKey($ids)->pluck('guardians.id')
                ->mapWithKeys(fn ($id) => [$id => route('guardians.show', $id)]),
            'ProgressRecord' => fn (array $ids) => ProgressRecord::where('teacher_id', $teacher->id)->whereKey($ids)->pluck('id')
                ->mapWithKeys(fn ($id) => [$id => route('progress.show', $id)]),
            'ChecklistItem' => fn (array $ids) => EccdChecklistItem::whereKey($ids)->pluck('id')
                ->mapWithKeys(fn ($id) => [$id => route('checklist.edit', $id)]),
            'Teacher' => fn (array $ids) => collect(in_array($teacher->id, $ids, true) ? [$teacher->id => route('settings.profile')] : []),
        ];

        $links = [];
        foreach ($idsByType as $type => $ids) {
            if (isset($resolvers[$type])) {
                foreach ($resolvers[$type]($ids) as $id => $url) {
                    $links["{$type}:{$id}"] = $url;
                }
            }
        }

        return $links;
    }

    public function exportCsv(User $user, array $filters): StreamedResponse
    {
        $query = $this->filteredQuery($user, $filters);
        $count = (clone $query)->count();
        recordLog('exported', 'Log', null, "Exported {$count} log ".($count === 1 ? 'entry' : 'entries').' to CSV.');

        $filename = 'littlestep-activity-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM so spreadsheet apps keep accents and ñ.
            fputcsv($output, ['Date', 'Time', 'Action', 'Category', 'Record', 'Record ID', 'Details', 'IP Address', 'Device']);

            foreach ($query->cursor() as $log) {
                $category = AuditLog::actionCategory($log->action);
                fputcsv($output, array_map([$this, 'csvSafe'], [
                    $log->created_at->format('Y-m-d'),
                    $log->created_at->format('H:i:s'),
                    AuditLog::actionLabel($log->action),
                    $category ? AuditLog::CATEGORIES[$category] : '',
                    AuditLog::entityLabel($log->entity_type),
                    $log->entity_id,
                    $log->details,
                    $log->ip_address,
                    AuditLog::describeDevice($log->user_agent),
                ]));
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    private function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], self::CSV_FORMULA_PREFIXES, true) ? "'".$value : $value;
    }
}
