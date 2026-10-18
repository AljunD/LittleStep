<?php

namespace App\Services;

use App\Models\ProgressRecord;
use App\Models\Domain;
use App\Models\DomainScore;
use App\Models\EccdChecklistItem;
use Carbon\Carbon;
use InvalidArgumentException;
use LogicException;

class EccdExpertSystem
{
    /** The 7 official ECCD domains, in Child's Record 2 order. */
    public const DOMAIN_LABELS = [
        'gross_motor' => 'Gross Motor',
        'fine_motor' => 'Fine Motor',
        'self_help' => 'Self-Help',
        'receptive_language' => 'Receptive Language',
        'expressive_language' => 'Expressive Language',
        'cognitive' => 'Cognitive',
        'social_emotional' => 'Social-Emotional',
    ];

    private const DOMAIN_KEYS = [
        'gross_motor',
        'fine_motor',
        'self_help',
        'receptive_language',
        'expressive_language',
        'cognitive',
        'social_emotional',
    ];

    /** Child's Record 2 age bands (Technical Manual §6), used to describe the band in explanations. */
    private const AGE_BAND_RANGES = [
        '3.1-4.0' => ['label' => '3.1–4.0', 'range' => '3 years 1 month to 4 years 0 months'],
        '4.1-5.0' => ['label' => '4.1–5.0', 'range' => '4 years 1 month to 5 years 0 months'],
        '5.1-5.11' => ['label' => '5.1–5.11', 'range' => '5 years 1 month to 5 years 11 months'],
    ];

    /** @var array<string, list<int>>|null checklist item ids per domain, loaded once per instance */
    private ?array $checklistItemIdsByDomain = null;

    public function compute(ProgressRecord $progressRecord, int $round = 1): array
    {
        $this->validateRound($round);

        $child    = $progressRecord->child;
        if (!$child || !$child->date_of_birth) {
            throw new LogicException('A progress record with a child date of birth is required to compute ECCD scores.');
        }

        return $this->computeFromAnswers(
            $child->date_of_birth,
            $this->getAssessmentDate($progressRecord, $round),
            $this->roundAnswers($progressRecord, $round)
        );
    }

    /**
     * Runs the official pipeline on a set of answers (age → raw → scaled → sum → standard → interpretation).
     * Used for saved results (compute()) and for the teacher's unsaved live preview.
     *
     * @param  array<int, string>  $answers  checklist item id => 'check' | 'hyphen'
     */
    public function computeFromAnswers($birthDate, $testDate, array $answers): array
    {
        $age = $testDate === null
            ? null
            : $this->computeAge($birthDate, $testDate);

        $rawScores = $this->rawScoresFromAnswers($answers);

        $scaledScores = $age === null
            ? array_fill_keys(self::DOMAIN_KEYS, null)
            : $this->convertToScaledScores($rawScores, $age);

        $hasAllScaledScores = count($scaledScores) === count(self::DOMAIN_KEYS)
            && !in_array(null, $scaledScores, true);
        $sumScaled = $hasAllScaledScores ? array_sum($scaledScores) : null;

        $standardScore = $this->convertToStandardScore($sumScaled);

        $interpretation = $this->interpret($standardScore, $scaledScores);

        return [
            'birth_date'       => Carbon::parse($birthDate)->toDateString(),
            'evaluation_date'  => $testDate === null ? null : Carbon::parse($testDate)->toDateString(),
            'age'              => $age,
            'raw_scores'       => $rawScores,
            'scaled_scores'    => $scaledScores,
            'sum_scaled_score' => $sumScaled,
            'standard_score'   => $standardScore,
            'interpretation'   => $interpretation,
        ];
    }

    public function computeAge($birthDate, $testDate): array
    {
        $birth = Carbon::parse($birthDate)->startOfDay();
        $test  = Carbon::parse($testDate)->startOfDay();

        if ($test->lt($birth)) {
            throw new InvalidArgumentException("The test date cannot be earlier than the child's date of birth.");
        }

        $years  = $test->year - $birth->year;
        $months = $test->month - $birth->month;
        $days   = $test->day - $birth->day;

        if ($days < 0) {
            $months--;
            $days += 30; 
        }
        if ($months < 0) {
            $years--;
            $months += 12;
        }

        return [
            'years'   => $years,
            'months'  => $months,
            'days'    => $days,
            'label'   => "{$years}y {$months}m",
            'decimal' => round($years + ($months / 12), 2),
            'band'    => $this->getAgeBand($years, $months),
        ];
    }

    private function getAgeBand(int $years, int $months): string
    {
        $totalMonths = ($years * 12) + $months;

        if ($totalMonths >= 37 && $totalMonths <= 48) {
            return '3.1-4.0';   // 3y1m – 4y0m
        }
        if ($totalMonths >= 49 && $totalMonths <= 60) {
            return '4.1-5.0';   // 4y1m – 5y0m
        }
        if ($totalMonths >= 61 && $totalMonths <= 71) {
            return '5.1-5.11';  // 5y1m – 5y11m
        }

        return 'unknown';
    }

    public function computeRawScores(ProgressRecord $progressRecord, int $round): array
    {
        $this->validateRound($round);

        return $this->rawScoresFromAnswers($this->roundAnswers($progressRecord, $round));
    }

    /**
     * The saved ✓ / — answers for one round. Only completed entries count, as in Child's Record 2.
     *
     * @return array<int, string> checklist item id => 'check' | 'hyphen'
     */
    public function roundAnswers(ProgressRecord $progressRecord, int $round): array
    {
        $this->validateRound($round);

        $answers = [];
        $domainItems = Domain::with('results')
            ->where('progress_record_id', $progressRecord->id)
            ->where('evaluation_round', $round)
            ->get();

        foreach ($domainItems as $domainItem) {
            $present = $this->normalizePresentValue($domainItem->results->first()?->present);
            if ($domainItem->status === 'completed' && $present !== null) {
                $answers[(int) $domainItem->checklist_item_id] = $present;
            }
        }

        return $answers;
    }

    /**
     * Raw score = number of ✓ per domain. A domain only gets a raw score once every one of its items is answered.
     *
     * @param  array<int, string>  $answers  checklist item id => 'check' | 'hyphen'
     */
    public function rawScoresFromAnswers(array $answers): array
    {
        $rawScores = array_fill_keys(self::DOMAIN_KEYS, null);

        foreach ($this->checklistItemIdsByDomain() as $domainKey => $itemIds) {
            if ($itemIds === []) {
                continue;
            }

            $rawScore = 0;
            foreach ($itemIds as $itemId) {
                $present = $answers[$itemId] ?? null;
                if ($present === null) {
                    continue 2;
                }
                if ($present === 'check') {
                    $rawScore++;
                }
            }

            $rawScores[$domainKey] = $rawScore;
        }

        return $rawScores;
    }

    /**
     * Step-by-step account of how a result was reached (the expert system's explanation facility).
     * Shared by the teacher web pages, the live preview and the guardian API.
     *
     * @param  array  $computed  output of compute() / computeFromAnswers()
     * @param  array<int, string>  $answers  the answers that produced $computed
     */
    public function explain(array $computed, array $answers): array
    {
        $age = $computed['age'] ?? null;
        $band = $age ? (self::AGE_BAND_RANGES[$age['band']] ?? null) : null;
        $itemIdsByDomain = $this->checklistItemIdsByDomain();

        $domains = [];
        $incomplete = [];
        $answeredTotal = 0;
        $warnings = [];

        foreach (self::DOMAIN_LABELS as $domainKey => $label) {
            $itemIds = $itemIdsByDomain[$domainKey] ?? [];
            $answered = count(array_filter($itemIds, fn (int $id) => isset($answers[$id])));
            $present = count(array_filter($itemIds, fn (int $id) => ($answers[$id] ?? null) === 'check'));
            $raw = $computed['raw_scores'][$domainKey] ?? null;
            $scaled = $computed['scaled_scores'][$domainKey] ?? null;
            $answeredTotal += $answered;

            $domains[$domainKey] = [
                'label' => $label,
                'items_total' => count($itemIds),
                'items_answered' => $answered,
                'items_present' => $present,
                'raw_score' => $raw,
                'scaled_score' => $scaled,
                'interpretation' => $scaled === null ? null : $this->interpretScaledScore($scaled),
            ];

            if ($itemIds !== [] && $answered < count($itemIds)) {
                $incomplete[] = "{$label} ({$answered}/".count($itemIds).')';
            }

            if ($raw !== null && $band !== null && $scaled === null) {
                $warnings[] = "{$label}: raw score {$raw} has no entry in the scaled-score table for age band {$band['label']}.";
            }
        }

        $scoredDomains = count(array_filter($computed['raw_scores'] ?? [], fn ($raw) => $raw !== null));
        $sumScaled = $computed['sum_scaled_score'] ?? null;
        $standardScore = $computed['standard_score'] ?? null;
        $overall = $standardScore === null ? null : ($computed['interpretation']['overall'] ?? null);

        $steps = [];
        if ($age === null) {
            $steps[] = 'No assessment date is set for this round yet, so the child\'s age at test cannot be computed.';
            $warnings[] = 'Set the assessment date for this round to compute the scores.';
        } else {
            $steps[] = sprintf(
                'Age at test: %d years %d months %d days, counted from the birth date (%s) to the test date (%s) with every month counted as 30 days.',
                $age['years'], $age['months'], $age['days'], $computed['birth_date'] ?? '—', $computed['evaluation_date']
            );
            if ($band !== null) {
                $steps[] = "Age band {$band['label']} ({$band['range']}) selects which official scaled-score table is used.";
            } else {
                $warnings[] = "The child's age at test ({$age['label']}) is outside Child's Record 2 (3 years 1 month to 5 years 11 months), so no scaled scores can be computed.";
            }
        }

        $steps[] = "Raw score: the number of ✓ items in a domain. A domain is scored once all of its items are answered ({$scoredDomains} of 7 domains so far).";
        $steps[] = 'Scaled score: each raw score is converted to a scaled score from 1 to 19 using the official table for the age band. 7–13 is average.';
        $steps[] = $sumScaled !== null
            ? "Sum of the 7 scaled scores: {$sumScaled}."
            : 'Sum of scaled scores: available once all 7 domains have a scaled score.';
        $steps[] = $standardScore !== null
            ? "Standard score: {$standardScore}, which means: {$overall}."
            : 'Standard score: available once the sum of scaled scores is known.';

        if ($answeredTotal === 0) {
            $warnings[] = 'No checklist results have been recorded for this round yet.';
        } elseif ($incomplete !== []) {
            $warnings[] = 'Not all items are answered yet: '.implode(', ', $incomplete).'.';
        }

        return [
            'evaluation_date' => $computed['evaluation_date'] ?? null,
            'age' => $age === null ? null : [
                'label' => $age['label'],
                'years' => $age['years'],
                'months' => $age['months'],
                'days' => $age['days'],
                'band' => $band['label'] ?? null,
                'band_range' => $band['range'] ?? null,
            ],
            'domains' => $domains,
            'scored_domains' => $scoredDomains,
            'sum_scaled_score' => $sumScaled,
            'standard_score' => $standardScore,
            'overall' => $overall,
            'interpretation_code' => $computed['interpretation']['code'] ?? null,
            'steps' => $steps,
            'warnings' => $warnings,
        ];
    }

    /** explain() for a saved round. */
    public function explainRound(ProgressRecord $progressRecord, int $round): array
    {
        $this->validateRound($round);

        $child = $progressRecord->child;
        if (!$child || !$child->date_of_birth) {
            throw new LogicException('A progress record with a child date of birth is required to explain ECCD scores.');
        }

        $answers = $this->roundAnswers($progressRecord, $round);

        return $this->explain(
            $this->computeFromAnswers($child->date_of_birth, $this->getAssessmentDate($progressRecord, $round), $answers),
            $answers
        );
    }

    /**
     * Skills to practise next: for each domain, the official checklist items marked — (not yet present)
     * in the latest round where that domain was assessed. Nothing is invented; it is the checklist itself.
     *
     * @return array<string, array{label: string, round: int, items: list<array{id: int, item_number: int|null, activity: string}>}>
     */
    public function skillsToPractice(ProgressRecord $progressRecord): array
    {
        $rows = Domain::with(['results', 'checklistItem'])
            ->where('progress_record_id', $progressRecord->id)
            ->get();

        // [domain][round] => list of answered rows
        $answered = [];
        foreach ($rows as $row) {
            $domainKey = $row->checklistItem?->domain;
            $present = $this->normalizePresentValue($row->results->first()?->present);
            if (!isset(self::DOMAIN_LABELS[$domainKey]) || $row->status !== 'completed' || $present === null) {
                continue;
            }
            $answered[$domainKey][(int) $row->evaluation_round][] = ['row' => $row, 'present' => $present];
        }

        $skills = [];
        foreach (self::DOMAIN_LABELS as $domainKey => $label) {
            if (empty($answered[$domainKey])) {
                continue;
            }

            $latestRound = max(array_keys($answered[$domainKey]));
            $items = collect($answered[$domainKey][$latestRound])
                ->filter(fn (array $entry) => $entry['present'] === 'hyphen')
                ->map(fn (array $entry) => [
                    'id' => $entry['row']->checklistItem->id,
                    'item_number' => $entry['row']->checklistItem->item_number,
                    'activity' => $entry['row']->checklistItem->activity,
                ])
                ->sortBy([['item_number', 'asc'], ['id', 'asc']])
                ->values()
                ->all();

            if ($items !== []) {
                $skills[$domainKey] = ['label' => $label, 'round' => $latestRound, 'items' => $items];
            }
        }

        return $skills;
    }

    /** @return array<string, list<int>> */
    private function checklistItemIdsByDomain(): array
    {
        if ($this->checklistItemIdsByDomain === null) {
            $this->checklistItemIdsByDomain = array_fill_keys(self::DOMAIN_KEYS, []);
            EccdChecklistItem::whereIn('domain', self::DOMAIN_KEYS)
                ->orderBy('id')
                ->get(['id', 'domain'])
                ->each(function (EccdChecklistItem $item) {
                    $this->checklistItemIdsByDomain[$item->domain][] = (int) $item->id;
                });
        }

        return $this->checklistItemIdsByDomain;
    }

    public function normalizePresentValue(mixed $value): ?string
    {
        if ($value === true || $value === 1) {
            return 'check';
        }

        if ($value === false || $value === 0) {
            return 'hyphen';
        }

        if (!is_string($value)) {
            return null;
        }

        return match (strtolower(trim($value))) {
            'check', '✓', '1', 'present' => 'check',
            'hyphen', '-', '–', '—', '0' => 'hyphen',
            default => null,
        };
    }

    public function isPresentValue(mixed $value): bool
    {
        return $this->normalizePresentValue($value) === 'check';
    }

    private function getAssessmentDate(ProgressRecord $progressRecord, int $round): ?string
    {
        $date = $progressRecord->getAttribute("evaluation_date_round_{$round}");

        if ($date === null && $round === 1) {
            $date = $progressRecord->evaluation_date;
        }

        return $date === null ? null : Carbon::parse($date)->toDateString();
    }

    private function validateRound(int $round): void
    {
        if ($round < 1 || $round > 3) {
            throw new InvalidArgumentException('ECCD evaluation round must be 1, 2, or 3.');
        }
    }

    public function convertToScaledScores(array $rawScores, array $age): array
    {
        $scaled = [];
        $band   = $age['band'];

        foreach ($rawScores as $domain => $raw) {
            $scaled[$domain] = $raw === null
                ? null
                : $this->lookupScaledScore($domain, $raw, $band);
        }

        return $scaled;
    }

    private function lookupScaledScore(string $domain, int $raw, string $band): ?int
    {
        $tables = $this->getScaledScoreTables();

        if (!isset($tables[$band][$domain])) {
            return null;
        }

        foreach ($tables[$band][$domain] as $scaled => $rawRange) {
            if ($rawRange === null) {
                continue;
            }

            if (is_array($rawRange)) {
                if ($raw >= $rawRange[0] && $raw <= $rawRange[1]) {
                    return (int) $scaled;
                }
            } else {
                if ($raw === $rawRange) {
                    return (int) $scaled;
                }
            }
        }

        return null;
    }

    private function getScaledScoreTables(): array
    {
        return [
            '3.1-4.0' => [
                'gross_motor' => [
                    1 => [0, 3], 2 => 4, 3 => 5, 5 => 6, 6 => 7,
                    7 => 8, 8 => 9, 10 => 10, 11 => 11, 12 => 12, 14 => 13,
                ],
                'fine_motor' => [
                    2 => [0, 3], 4 => 4, 5 => 5, 7 => 6, 9 => 7,
                    10 => 8, 12 => 9, 14 => 10, 15 => 11,
                ],
                'self_help' => [
                    1 => [0, 9], 2 => 10, 3 => 11, 4 => 12, 5 => [13, 14],
                    6 => 15, 7 => 16, 8 => 17, 9 => [18, 19], 10 => 20,
                    11 => 21, 12 => 22, 13 => [23, 24], 14 => 25, 15 => 26, 16 => 27,
                ],
                'receptive_language' => [
                    3 => [0, 1], 5 => 2, 7 => 3, 10 => 4, 12 => 5,
                ],
                'expressive_language' => [
                    1 => [0, 2], 3 => 3, 4 => 4, 6 => 5, 9 => 6, 11 => 7, 13 => 8,
                ],
                'cognitive' => [
                    3 => 0, 4 => 1, 5 => [2, 3], 6 => 4, 7 => 5, 8 => 6,
                    9 => 7, 10 => [8, 9], 11 => 10, 12 => 11, 13 => 12,
                    14 => [13, 14], 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => [19, 21],
                ],
                'social_emotional' => [
                    1 => [0, 9], 2 => [10, 11], 3 => 12, 4 => 13, 5 => 14,
                    6 => 15, 7 => 16, 8 => [17, 18], 9 => 19, 10 => 20,
                    11 => 21, 12 => 22, 13 => 23, 14 => 24,
                ],
            ],

            '4.1-5.0' => [
                'gross_motor' => [
                    1 => [0, 5], 2 => 6, 4 => 7, 5 => 8, 7 => 9,
                    8 => 10, 10 => 11, 11 => 12, 13 => 13,
                ],
                'fine_motor' => [
                    1 => [0, 3], 2 => 4, 4 => 5, 5 => 6, 7 => 7,
                    9 => 8, 10 => 9, 12 => 10, 14 => 11,
                ],
                'self_help' => [
                    1 => [0, 15], 2 => 16, 3 => 17, 4 => 18, 5 => 19,
                    6 => 20, 8 => 21, 9 => 22, 10 => 23, 11 => 24,
                    12 => 25, 13 => 26, 14 => 27,
                ],
                'receptive_language' => [
                    1 => [0, 1], 3 => 2, 6 => 3, 9 => 4, 11 => 5,
                ],
                'expressive_language' => [
                    2 => [0, 5], 5 => 6, 8 => 7, 11 => 8,
                ],
                'cognitive' => [
                    1 => 0, 2 => 1, 3 => [2, 3], 4 => 4, 5 => 5,
                    6 => [6, 7], 7 => 8, 8 => [9, 10], 9 => 11, 10 => 12,
                    11 => [13, 14], 12 => 15, 13 => [16, 17], 14 => 18,
                    15 => [19, 20], 16 => 21,
                ],
                'social_emotional' => [
                    1 => [0, 13], 2 => 14, 3 => 15, 4 => 16, 5 => 17,
                    7 => 18, 8 => 19, 9 => 20, 10 => 21, 11 => 22,
                    12 => 23, 13 => 24,
                ],
            ],

            '5.1-5.11' => [
                'gross_motor' => [
                    1 => [0, 10], 4 => 11, 7 => 12, 11 => 13,
                ],
                'fine_motor' => [
                    1 => [0, 5], 3 => 6, 5 => 7, 7 => 8, 8 => 9,
                    10 => 10, 12 => 11,
                ],
                'self_help' => [
                    2 => [0, 19], 3 => 20, 4 => 21, 6 => 22, 7 => 23,
                    9 => 24, 10 => 25, 12 => 26, 13 => 27,
                ],
                'receptive_language' => [
                    1 => [0, 2], 4 => 3, 8 => 4, 11 => 5,
                ],
                'expressive_language' => [
                    5 => [0, 7], 11 => 8,
                ],
                'cognitive' => [
                    1 => [0, 9], 2 => 10, 3 => 11, 4 => 12, 5 => 13,
                    6 => 14, 7 => 15, 8 => 16, 9 => 17, 10 => 18,
                    11 => 19, 12 => 20, 13 => 21,
                ],
                'social_emotional' => [
                    1 => [0, 15], 2 => 16, 3 => 17, 5 => 18, 6 => 19,
                    7 => 20, 9 => 21, 10 => 22, 11 => 23, 13 => 24,
                ],
            ],
        ];
    }

    public function convertToStandardScore(?int $sumScaled): ?int
    {
        if ($sumScaled === null) {
            return null;
        }

        $table = [
            29 => 37, 30 => 38, 31 => 40, 32 => 41, 33 => 43, 34 => 44,
            35 => 45, 36 => 47, 37 => 48, 38 => 50, 39 => 51, 40 => 53,
            41 => 54, 42 => 56, 43 => 57, 44 => 59, 45 => 60, 46 => 62,
            47 => 63, 48 => 65, 49 => 66, 50 => 67, 51 => 69, 52 => 70,
            53 => 72, 54 => 73, 55 => 75, 56 => 76, 57 => 78, 58 => 79,
            59 => 81, 60 => 82, 61 => 84, 62 => 85, 63 => 86, 64 => 88,
            65 => 89, 66 => 91, 67 => 92, 68 => 94, 69 => 95, 70 => 97,
            71 => 98, 72 => 100, 73 => 101, 74 => 103, 75 => 104,
            76 => 105, 77 => 107, 78 => 108, 79 => 110, 80 => 111,
            81 => 113, 82 => 114, 83 => 116, 84 => 117, 85 => 119,
            86 => 120, 87 => 122, 88 => 123, 89 => 124, 90 => 126,
            91 => 127, 92 => 129, 93 => 130, 94 => 132, 95 => 133,
            96 => 135, 97 => 136, 98 => 138,
        ];

        return $table[$sumScaled] ?? null;
    }

    public function interpret(?int $standardScore, array $scaledScores): array
    {
        // Overall interpretation from Standard Score
        $overall = match (true) {
            $standardScore === null   => 'Insufficient data',
            $standardScore <= 69      => 'Suggest significant delay in overall development',
            $standardScore <= 79      => 'Suggest slight delay in overall development',
            $standardScore <= 119     => 'Average overall development',
            $standardScore <= 129     => 'Suggests slightly advanced development',
            default                   => 'Suggest highly advanced development',
        };

        $perDomain = [];
        foreach ($scaledScores as $domain => $scaled) {
            $perDomain[$domain] = $scaled === null ? 'No data' : $this->interpretScaledScore($scaled);
        }

        return [
            'overall'    => $overall,
            'per_domain' => $perDomain,
            'code'       => $this->mapInterpretationCode($standardScore),
        ];
    }

    /** Technical Manual Table 6: interpretation of one domain's scaled score. */
    public function interpretScaledScore(int $scaled): string
    {
        return match (true) {
            $scaled <= 3  => 'Suggest significant delay',
            $scaled <= 6  => 'Suggest slight delay',
            $scaled <= 13 => 'Average development',
            $scaled <= 16 => 'Suggest slightly advanced development',
            default       => 'Suggest highly advanced development',
        };
    }

    private function mapInterpretationCode(?int $standardScore): ?string
    {
        return match (true) {
            $standardScore === null => null,
            $standardScore <= 69 => 'monitor_3m',
            $standardScore <= 79 => 'monitor_6m',
            $standardScore <= 119 => 'average',
            $standardScore <= 129 => 'slightly_advanced',
            default => 'highly_advanced',
        };
    }

    public function saveScores(ProgressRecord $progressRecord, int $round, array $result): void
    {
        $this->validateRound($round);

        foreach ($result['raw_scores'] ?? [] as $domainKey => $raw) {
            $domainRow = Domain::where('progress_record_id', $progressRecord->id)
                ->where('evaluation_round', $round)
                ->whereHas('checklistItem', fn($q) => $q->where('domain', $domainKey))
                ->orderBy('id')
                ->first();

            if (!$domainRow) {
                continue;
            }

            DomainScore::updateOrCreate(
                [
                    'progress_record_id' => $progressRecord->id,
                    'domain_id'          => $domainRow->id,
                ],
                [
                    'raw_score'           => $raw,
                    'scaled_score'        => $result['scaled_scores'][$domainKey] ?? null,
                    'sum_scaled_score'    => $result['sum_scaled_score'] ?? null,
                    'standard_score'      => $result['standard_score'] ?? null,
                    'child_age'           => $result['age']['decimal'] ?? null,
                    'interpretation_code' => $result['interpretation']['code'] ?? null,
                ]
            );
        }
    }
}