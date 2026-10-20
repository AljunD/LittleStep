<?php

namespace App\Services;

use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class-wide insights for the teacher dashboard, built from the saved scores of each child's latest scored evaluation.
 * Only the teacher's own active (not archived) records are included.
 */
class DashboardInsightsService
{
    /** Interpretation codes in official order (lowest to highest standard score), with short dashboard wording. */
    public const OVERALL = [
        'monitor_3m' => ['label' => 'Significant delay', 'color' => 'red', 'bar' => 'bg-rose-400'],
        'monitor_6m' => ['label' => 'Slight delay', 'color' => 'amber', 'bar' => 'bg-amber-400'],
        'average' => ['label' => 'Average', 'color' => 'green', 'bar' => 'bg-emerald-400'],
        'slightly_advanced' => ['label' => 'Slightly advanced', 'color' => 'sky', 'bar' => 'bg-sky-400'],
        'highly_advanced' => ['label' => 'Highly advanced', 'color' => 'violet', 'bar' => 'bg-violet-400'],
    ];

    /** Months until the next check for the codes that call for monitoring. */
    public const FOLLOW_UP_MONTHS = ['monitor_3m' => 3, 'monitor_6m' => 6];

    public function __construct(private EccdExpertSystem $expertSystem) {}

    public function for(Teacher $teacher, int $followUpLimit = 5): array
    {
        $latest = $this->latestRoundScores($teacher);
        $followUps = $this->followUps($latest);

        return [
            'scored_children' => $latest->count(),
            'overall' => $this->overall($latest),
            'domains' => $this->domains($latest),
            'follow_ups' => $followUps->take($followUpLimit),
            'follow_ups_total' => $followUps->count(),
        ];
    }

    /** One entry per record: its latest scored round's per-domain scaled scores and overall result. */
    private function latestRoundScores(Teacher $teacher): Collection
    {
        $rows = DB::table('domain_scores')
            ->join('domains', 'domain_scores.domain_id', '=', 'domains.id')
            ->join('eccd_checklist_items', 'domains.checklist_item_id', '=', 'eccd_checklist_items.id')
            ->join('progress_records', 'domain_scores.progress_record_id', '=', 'progress_records.id')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->where('progress_records.teacher_id', $teacher->id)
            ->whereNull('domain_scores.deleted_at')
            ->whereNull('domains.deleted_at')
            ->whereNull('progress_records.deleted_at')
            ->whereNull('children.deleted_at')
            ->select(
                'progress_records.id as record_id',
                'domains.evaluation_round as round',
                'eccd_checklist_items.domain',
                'domain_scores.scaled_score',
                'domain_scores.standard_score',
                'domain_scores.interpretation_code',
                'progress_records.evaluation_date',
                'progress_records.evaluation_date_round_1',
                'progress_records.evaluation_date_round_2',
                'progress_records.evaluation_date_round_3',
                'children.first_name',
                'children.middle_name',
                'children.last_name'
            )
            ->get();

        return $rows->groupBy('record_id')->map(function (Collection $recordRows) {
            $round = (int) $recordRows->max('round');
            $roundRows = $recordRows->where('round', $round);
            $first = $roundRows->first();
            $date = $first->{"evaluation_date_round_{$round}"} ?? ($round === 1 ? $first->evaluation_date : null);

            return (object) [
                'record_id' => (int) $first->record_id,
                'child_name' => trim(preg_replace('/\s+/', ' ', "{$first->first_name} {$first->middle_name} {$first->last_name}")),
                'round' => $round,
                'evaluated_on' => $date ? Carbon::parse($date)->startOfDay() : null,
                'interpretation_code' => $roundRows->pluck('interpretation_code')->filter()->first(),
                'scaled' => $roundRows->filter(fn ($row) => $row->scaled_score !== null)
                    ->mapWithKeys(fn ($row) => [$row->domain => (int) $row->scaled_score])
                    ->all(),
            ];
        })->values();
    }

    private function overall(Collection $latest): array
    {
        $counts = $latest->pluck('interpretation_code')->filter()->countBy();
        $total = max(1, $counts->sum());

        return collect(self::OVERALL)->map(fn (array $meta, string $code) => $meta + [
            'count' => $counts->get($code, 0),
            'percent' => (int) round($counts->get($code, 0) / $total * 100),
        ])->all();
    }

    /** Per domain: average scaled score and how many children score in the delay range, most in need of support first. */
    private function domains(Collection $latest): array
    {
        return collect(EccdExpertSystem::DOMAIN_LABELS)
            ->map(function (string $label, string $key) use ($latest) {
                $scores = $latest->map(fn ($record) => $record->scaled[$key] ?? null)->filter(fn ($score) => $score !== null);

                return [
                    'key' => $key,
                    'label' => $label,
                    'scored' => $scores->count(),
                    'average' => $scores->isEmpty() ? null : round($scores->avg(), 1),
                    'needs_support' => $scores->filter(fn (int $score) => $this->expertSystem->scaledScoreSuggestsDelay($score))->count(),
                ];
            })
            ->sortBy([['needs_support', 'desc'], [fn ($domain) => $domain['average'] ?? INF, 'asc']])
            ->values()
            ->all();
    }

    /** Children whose latest overall result calls for monitoring, earliest due check first. */
    private function followUps(Collection $latest): Collection
    {
        $today = Carbon::today();

        return $latest
            ->filter(fn ($record) => isset(self::FOLLOW_UP_MONTHS[$record->interpretation_code]))
            ->map(function ($record) use ($today) {
                $due = $record->evaluated_on?->copy()->addMonthsNoOverflow(self::FOLLOW_UP_MONTHS[$record->interpretation_code]);

                return (object) [
                    'record_id' => $record->record_id,
                    'child_name' => $record->child_name,
                    'round' => $record->round,
                    'interpretation_code' => $record->interpretation_code,
                    'months' => self::FOLLOW_UP_MONTHS[$record->interpretation_code],
                    'due_on' => $due,
                    'overdue' => $due !== null && $due->lt($today),
                ];
            })
            ->sortBy(fn ($followUp) => $followUp->due_on?->timestamp ?? PHP_INT_MAX)
            ->values();
    }
}
