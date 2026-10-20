<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use Carbon\Carbon;

/**
 * The extra details on a teacher's child profile page: age, the teacher's ECCD evaluations of the child
 * (scored by EccdExpertSystem, as on the progress record page), skills to practice and observation notes.
 */
class ChildProfileSummary
{
    public const EVALUATION_LABELS = [1 => '1st Evaluation', 2 => '2nd Evaluation', 3 => '3rd Evaluation'];

    /** domain_observations columns shown on the profile, with their labels. */
    public const OBSERVATION_FIELDS = [
        'place_administered' => 'Place administered',
        'background_observations' => 'Background observations',
        'family_environment_notes' => 'Family environment',
        'stimulating_activities_notes' => 'Stimulating activities',
        'home_environment_notes' => 'Home environment',
        'other_notes' => 'Other notes',
    ];

    public function __construct(private EccdExpertSystem $expertSystem) {}

    public function for(Teacher $teacher, Child $child): array
    {
        // Only this teacher's active record: an archived record is not shown as current progress.
        $record = $teacher->progressRecords()->where('child_id', $child->id)->with('domainObservation')->first();
        $evaluations = $record ? $this->evaluations($record) : [];
        $latest = collect($evaluations)->filter(fn (array $evaluation) => $evaluation['scored_domains'] > 0)->last();

        return [
            // 30-day months, as in the official age computation; never earlier than the birth date.
            'age' => $this->expertSystem->computeAge($child->date_of_birth, Carbon::today()->max(Carbon::parse($child->date_of_birth))),
            'record' => $record,
            'evaluations' => $evaluations,
            'evaluations_done' => collect($evaluations)->where('date', '!==', null)->count(),
            'latest' => $latest,
            'skills_to_practice' => $record ? collect($this->expertSystem->skillsToPractice($record))->sum(fn (array $domain) => count($domain['items'])) : 0,
            'observations' => $this->observations($record),
        ];
    }

    private function evaluations(ProgressRecord $record): array
    {
        $evaluations = [];
        foreach (self::EVALUATION_LABELS as $number => $label) {
            $date = $record->getAttribute("evaluation_date_round_{$number}") ?? ($number === 1 ? $record->evaluation_date : null);
            $computed = $date ? $this->expertSystem->compute($record, $number) : null;
            $scaled = $computed['scaled_scores'] ?? [];

            $evaluations[$number] = [
                'number' => $number,
                'label' => $label,
                'date' => $date ? Carbon::parse($date) : null,
                'age' => $computed['age']['label'] ?? null,
                'scored_domains' => count(array_filter($scaled, fn ($score) => $score !== null)),
                'standard_score' => $computed['standard_score'] ?? null,
                'interpretation_code' => $computed['interpretation']['code'] ?? null,
                'overall' => $computed['interpretation']['overall'] ?? null,
                'domains' => collect(EccdExpertSystem::DOMAIN_LABELS)->map(fn (string $domainLabel, string $key) => [
                    'label' => $domainLabel,
                    'scaled' => $scaled[$key] ?? null,
                    'interpretation' => isset($scaled[$key]) ? $this->expertSystem->interpretScaledScore($scaled[$key]) : null,
                    'suggests_delay' => isset($scaled[$key]) && $this->expertSystem->scaledScoreSuggestsDelay($scaled[$key]),
                ])->all(),
            ];
        }

        return $evaluations;
    }

    private function observations(?ProgressRecord $record): array
    {
        $observation = $record?->domainObservation;
        if (! $observation) {
            return [];
        }

        return collect(self::OBSERVATION_FIELDS)
            ->map(fn (string $label, string $field) => ['label' => $label, 'text' => trim((string) $observation->getAttribute($field))])
            ->filter(fn (array $note) => $note['text'] !== '')
            ->values()
            ->all();
    }
}
