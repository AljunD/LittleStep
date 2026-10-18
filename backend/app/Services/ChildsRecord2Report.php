<?php

namespace App\Services;

use App\Models\ProgressRecord;
use Illuminate\Support\Carbon;

/**
 * Builds the downloadable ECCD Checklist, Child's Record 2 (20 pages, US Letter).
 *
 * Each page uses the official form as its background (resources/pdf/childs-record-2/page-NN.jpg, taken from the
 * official Child's Record 2 PDF) and overlays the child's data at the positions measured on that form, in PDF points
 * (612 × 792, origin top-left). The view only paints what this class returns.
 */
class ChildsRecord2Report
{
    public const PAGE_WIDTH = 612;

    public const PAGE_HEIGHT = 792;

    private const ROUNDS = [1, 2, 3];

    private const ROUND_LABELS = [1 => '1st', 2 => '2nd', 3 => '3rd'];

    /**
     * Checklist pages 4–16: Present column centres (1st/2nd/3rd Eval), Comments column, TOTAL SCORE row and
     * the [top, bottom] of every item row, measured on the official form.
     */
    private const CHECKLIST_PAGES = [
        4 => ['domain' => 'gross_motor', 'present' => [415.7, 443.0, 469.6], 'comments' => [483.3, 567.4], 'total' => null,
            'rows' => [1 => [105.3, 144.2], 2 => [144.2, 225.8], 3 => [225.8, 299.4], 4 => [299.4, 348.1], 5 => [348.1, 439.3], 6 => [439.3, 537.8], 7 => [537.8, 657.8], 8 => [657.8, 696.5], 9 => [696.5, 740.2]]],
        5 => ['domain' => 'gross_motor', 'present' => [416.0, 447.0, 475.7], 'comments' => [488.8, 567.4], 'total' => [348.0, 389.7],
            'rows' => [10 => [86.0, 186.8], 11 => [186.8, 260.0], 12 => [260.0, 308.8], 13 => [308.8, 344.4]]],
        6 => ['domain' => 'fine_motor', 'present' => [416.6, 443.3, 470.0], 'comments' => [483.3, 566.9], 'total' => null,
            'rows' => [1 => [109.4, 206.3], 2 => [206.3, 292.0], 3 => [292.0, 377.0], 4 => [377.0, 410.4], 5 => [410.4, 505.6], 6 => [505.6, 553.0], 7 => [553.0, 738.5]]],
        7 => ['domain' => 'fine_motor', 'present' => [417.4, 445.5, 474.4], 'comments' => [488.2, 568.6], 'total' => [494.0, 539.9],
            'rows' => [8 => [87.0, 209.4], 9 => [209.4, 305.0], 10 => [305.0, 401.2], 11 => [401.2, 490.4]]],
        8 => ['domain' => 'self_help', 'present' => [413.9, 440.6, 470.0], 'comments' => [483.4, 566.9], 'total' => null,
            'rows' => [1 => [110.0, 152.0], 2 => [152.0, 194.8], 3 => [194.8, 228.4], 4 => [228.4, 264.4], 5 => [264.4, 295.8], 6 => [295.8, 338.4], 7 => [338.4, 372.2], 8 => [372.2, 408.7], 9 => [408.7, 437.5], 10 => [437.5, 471.7], 11 => [471.7, 502.8], 12 => [502.8, 546.7], 13 => [546.7, 604.6], 14 => [626.4, 671.9], 15 => [671.9, 703.1], 16 => [703.1, 740.2]]],
        9 => ['domain' => 'self_help', 'present' => [414.1, 440.0, 467.5], 'comments' => [480.4, 567.4], 'total' => [715.4, 742.4],
            'rows' => [17 => [85.3, 118.8], 18 => [118.8, 164.9], 19 => [197.5, 275.2], 20 => [275.2, 350.5], 21 => [350.5, 416.9], 22 => [416.9, 493.0], 23 => [493.0, 530.5], 24 => [547.9, 592.0], 25 => [592.0, 638.5], 26 => [638.5, 682.4], 27 => [682.4, 711.8]]],
        10 => ['domain' => 'receptive_language', 'present' => [415.6, 441.6, 468.4], 'comments' => [481.4, 566.9], 'total' => [455.2, 493.8],
            'rows' => [1 => [113.5, 147.8], 2 => [147.8, 200.0], 3 => [200.0, 282.0], 4 => [282.0, 365.4], 5 => [365.4, 451.6]]],
        11 => ['domain' => 'expressive_language', 'present' => [416.9, 442.9, 470.2], 'comments' => [483.2, 567.4], 'total' => [525.8, 565.2],
            'rows' => [1 => [112.8, 162.4], 2 => [162.4, 199.9], 3 => [199.9, 250.7], 4 => [250.7, 326.3], 5 => [326.3, 376.2], 6 => [376.2, 401.4], 7 => [401.4, 440.2], 8 => [440.2, 522.2]]],
        12 => ['domain' => 'cognitive', 'present' => [416.0, 440.5, 467.5], 'comments' => [479.7, 568.5], 'total' => null,
            'rows' => [1 => [115.9, 186.0], 2 => [186.0, 262.4], 3 => [262.4, 298.2], 4 => [298.2, 335.5], 5 => [335.5, 414.0], 6 => [414.0, 489.4], 7 => [489.4, 596.5], 8 => [596.5, 739.0]]],
        13 => ['domain' => 'cognitive', 'present' => [415.7, 440.2, 467.5], 'comments' => [479.7, 567.4], 'total' => null,
            'rows' => [9 => [85.7, 199.0], 10 => [199.0, 320.5], 11 => [320.5, 403.8], 12 => [403.8, 591.6], 13 => [591.6, 652.3], 14 => [652.3, 736.6]]],
        14 => ['domain' => 'cognitive', 'present' => [416.3, 442.3, 469.9], 'comments' => [482.9, 567.4], 'total' => [607.8, 653.6],
            'rows' => [15 => [86.7, 113.0], 16 => [113.0, 162.2], 17 => [162.2, 200.0], 18 => [200.0, 282.4], 19 => [282.4, 370.9], 20 => [370.9, 463.6], 21 => [463.6, 604.2]]],
        15 => ['domain' => 'social_emotional', 'present' => [415.7, 441.7, 470.0], 'comments' => [483.0, 567.4], 'total' => null,
            'rows' => [1 => [105.0, 145.2], 2 => [145.2, 191.5], 3 => [191.5, 234.4], 4 => [234.4, 274.8], 5 => [274.8, 309.5], 6 => [309.5, 347.9], 7 => [347.9, 373.1], 8 => [373.1, 417.6], 9 => [417.6, 446.4], 10 => [446.4, 481.3], 11 => [481.3, 519.7], 12 => [519.7, 586.6], 13 => [586.6, 623.5], 14 => [623.5, 738.5]]],
        16 => ['domain' => 'social_emotional', 'present' => [415.8, 440.3, 469.8], 'comments' => [482.0, 568.5], 'total' => [603.0, 635.5],
            'rows' => [15 => [87.0, 125.3], 16 => [125.3, 183.7], 17 => [183.7, 209.8], 18 => [209.8, 254.9], 19 => [254.9, 301.9], 20 => [301.9, 349.0], 21 => [349.0, 410.0], 22 => [410.0, 485.4], 23 => [485.4, 535.4], 24 => [535.4, 599.4]]],
    ];

    /** Page 19: dot x per domain (Child's Record 2 order) and the y of scaled scores 19 and 1, per evaluation panel. */
    private const SCALED_CHART = [
        1 => ['x' => [100.0, 119.0, 137.0, 157.3, 175.3, 194.4, 213.5], 'y19' => 244.9, 'y1' => 721.9, 'age' => [141.0, 212.4]],
        2 => ['x' => [269.3, 288.4, 306.4, 326.6, 344.6, 363.8, 382.9], 'y19' => 245.8, 'y1' => 722.8, 'age' => [309.7, 381.1]],
        3 => ['x' => [436.8, 456.0, 474.0, 494.2, 512.2, 531.4, 550.4], 'y19' => 245.4, 'y1' => 722.4, 'age' => [477.3, 548.7]],
    ];

    /** Page 20: y of each labelled standard score on the axis (the printed scale is not perfectly linear). */
    private const STANDARD_AXIS = [
        160 => 138.7, 150 => 178.9, 140 => 218.4, 130 => 259.3, 120 => 299.5, 110 => 339.0, 100 => 379.4,
        90 => 419.6, 80 => 459.1, 70 => 497.8, 60 => 539.3, 50 => 578.9, 40 => 619.8, 30 => 657.8, 20 => 694.4,
    ];

    /** Page 20 age columns ("3 years & 1 month", "4 years", "5 years"): [left, ruler centre, right]. */
    private const STANDARD_COLUMNS = [3 => [121.5, 195.5, 270.6], 4 => [270.6, 345.8, 419.5], 5 => [419.5, 494.4, 568.5]];

    public function __construct(private EccdExpertSystem $expert) {}

    /** @return list<array{template: string, items: list<array>, svg: string|null}> */
    public function build(ProgressRecord $progressRecord): array
    {
        $progressRecord->loadMissing(['child', 'teacher', 'domainObservation', 'domains.checklistItem', 'domains.results']);

        $child = $progressRecord->child;
        $examiner = $this->fullName($progressRecord->teacher);
        $computed = [];
        foreach (self::ROUNDS as $round) {
            $computed[$round] = $this->expert->compute($progressRecord, $round);
        }

        $answers = $this->answers($progressRecord);
        $pages = [];

        foreach (range(1, 20) as $number) {
            $items = match (true) {
                $number === 1 => $this->profilePage($child),
                $number === 2 => $this->agePage($computed, $examiner),
                isset(self::CHECKLIST_PAGES[$number]) => $this->checklistPage(self::CHECKLIST_PAGES[$number], $answers, $computed),
                $number === 17 => $this->notesPage($progressRecord, $examiner),
                $number === 18 => $this->summaryPage($computed),
                $number === 19 => $this->scaledAgeLabels($computed),
                $number === 20 => $this->standardDates($computed),
                default => [],
            };

            $pages[] = [
                'template' => resource_path(sprintf('pdf/childs-record-2/page-%02d.jpg', $number)),
                'items' => $items,
                'svg' => match ($number) {
                    19 => $this->scaledChartSvg($computed),
                    20 => $this->standardChartSvg($computed),
                    default => null,
                },
            ];
        }

        return $pages;
    }

    /** [domain][item_number][round] => ['mark' => '✔'|'—', 'comment' => string] */
    private function answers(ProgressRecord $progressRecord): array
    {
        $answers = [];
        foreach ($progressRecord->domains as $domain) {
            $item = $domain->checklistItem;
            $result = $domain->results->first();
            if (! $item || ! $result) {
                continue;
            }

            $present = $this->expert->normalizePresentValue($result->present);
            $answers[$item->domain][(int) $item->item_number][(int) $domain->evaluation_round] = [
                'mark' => match ($present) {
                    'check' => '✔',
                    'hyphen' => '—',
                    default => '',
                },
                'comment' => trim((string) $result->comments),
            ];
        }

        return $answers;
    }

    /** Page 1: Sociodemographic Profile. */
    private function profilePage($child): array
    {
        if (! $child) {
            return [];
        }

        $items = [
            $this->text(164, 435.5, 147, $this->fullName($child)),
            $this->text(338, 435.5, 30, $child->sex),
            $this->text(375, 567.5, 147, $child->is_studying ? $child->school_name : null),
            $this->text(171, 586.3, 140, $child->fathers_name),
            $this->text(375, 586.3, 147, $child->fathers_age),
            $this->text(194, 605.9, 117, $child->fathers_occupation),
            $this->text(461, 605.9, 61, $child->fathers_education, 7),
            $this->text(172, 625.1, 139, $child->mothers_name),
            $this->text(379, 625.1, 143, $child->mothers_age),
            $this->text(196, 644.3, 115, $child->mothers_occupation),
            $this->text(464, 644.3, 58, $child->mothers_education, 7),
            $this->text(218, 663.8, 93, $child->number_of_siblings),
            $this->text(487, 663.8, 35, $child->birth_order ? $this->ordinal((int) $child->birth_order) : null),
        ];

        if ($child->date_of_birth) {
            $birth = Carbon::parse($child->date_of_birth);
            $items[] = $this->text(425, 436, 44, $birth->format('F'), 7, 'center');
            $items[] = $this->text(472, 436, 17, $birth->format('j'), 7, 'center');
            $items[] = $this->text(498, 436, 26, $birth->format('Y'), 7, 'center');
        }

        // The address is stored as "Barangay, Municipality/City, Province, Region".
        $parts = array_map('trim', explode(',', (string) $child->address));
        if (count($parts) === 4) {
            foreach ([[140, 88], [228, 90], [318, 80], [398, 124]] as $index => [$left, $width]) {
                $items[] = $this->text($left, 474, $width, $parts[$index], 7.5, 'center');
            }
        } else {
            $items[] = $this->text(142, 474, 380, $child->address, 7.5);
        }

        $boxes = ['right' => [320.8, 516.5], 'left' => [374.3, 516.5], 'both' => [320.8, 535.4], 'not_yet_established' => [374.3, 535.4]];
        if (isset($boxes[$child->handedness])) {
            $items[] = $this->checkMark(...$boxes[$child->handedness]);
        }
        $items[] = $this->checkMark($child->is_studying ? 374.5 : 428.5, 553.9);

        return $items;
    }

    /** Page 2: Computation of the Child's Age (Year / Month / Day rows and examiner per assessment). */
    private function agePage(array $computed, string $examiner): array
    {
        $blocks = [1 => [343.0, 363.0, 382.7, 404.3], 2 => [404.3, 424.1, 444.2, 462.5], 3 => [462.5, 484.6, 504.8, 525.0]];
        $columns = [[199.1, 270.5], [270.5, 343.1], [343.1, 415.1]];
        $items = [];

        foreach ($blocks as $round => [$top, $birthTop, $ageTop, $bottom]) {
            $tested = $computed[$round]['evaluation_date'] ?? null;
            $age = $computed[$round]['age'] ?? null;
            if ($tested === null || $age === null) {
                continue;
            }

            $testDate = Carbon::parse($tested);
            $birthDate = Carbon::parse($computed[$round]['birth_date']);
            $rows = [
                [$top, $birthTop, [$testDate->year, $testDate->month, $testDate->day]],
                [$birthTop, $ageTop, [$birthDate->year, $birthDate->month, $birthDate->day]],
                [$ageTop, $bottom, [$age['years'], $age['months'], $age['days']]],
            ];
            foreach ($rows as [$rowTop, $rowBottom, $values]) {
                foreach ($columns as $index => [$left, $right]) {
                    $items[] = $this->centered($left, $right, $rowTop, $rowBottom, $values[$index], 9);
                }
            }
            $items[] = $this->centered(415.1, 569.0, $top, $bottom, $examiner, 8.5);
        }

        return $items;
    }

    /** Pages 4–16: ✔ / — per evaluation, teacher comments and the domain TOTAL SCORE. */
    private function checklistPage(array $layout, array $answers, array $computed): array
    {
        $items = [];
        [$commentsLeft, $commentsRight] = $layout['comments'];

        foreach ($layout['rows'] as $itemNumber => [$top, $bottom]) {
            $comments = [];
            foreach (self::ROUNDS as $index => $round) {
                $answer = $answers[$layout['domain']][$itemNumber][$round] ?? null;
                if (! $answer) {
                    continue;
                }
                if ($answer['mark'] !== '') {
                    $items[] = $this->mark($layout['present'][$index], ($top + $bottom) / 2, $answer['mark']);
                }
                if ($answer['comment'] !== '') {
                    $comments[] = self::ROUND_LABELS[$round].': '.$answer['comment'];
                }
            }

            if ($comments !== []) {
                $items[] = [
                    'left' => $commentsLeft + 2, 'top' => $top + 2,
                    'width' => $commentsRight - $commentsLeft - 4, 'height' => max($bottom - $top - 4, 8),
                    'text' => implode("\n", $comments), 'size' => 5.5, 'align' => 'left',
                ];
            }
        }

        if ($layout['total']) {
            [$top, $bottom] = $layout['total'];
            foreach (self::ROUNDS as $index => $round) {
                $raw = $computed[$round]['raw_scores'][$layout['domain']] ?? null;
                if ($raw !== null) {
                    $center = $layout['present'][$index];
                    $items[] = $this->centered($center - 13, $center + 13, $top, $bottom, $raw, 10, true);
                }
            }
        }

        return $items;
    }

    /** Page 17: examiner notes written on the form's ruled lines (16 pt apart). */
    private function notesPage(ProgressRecord $progressRecord, string $examiner): array
    {
        $observation = $progressRecord->domainObservation;
        $items = [
            $this->text(162, 52, 210, $examiner, 8),
            $this->text(165.5, 64, 208, $progressRecord->evaluation_date ? Carbon::parse($progressRecord->evaluation_date)->format('F j, Y') : null, 8),
            $this->text(171.8, 76, 150, $observation?->place_administered, 8),
        ];

        $sections = [
            'background_observations' => [201.1, 5],
            'family_environment_notes' => [317.1, 5],
            'stimulating_activities_notes' => [445.1, 4],
            'home_environment_notes' => [545.1, 5],
            'other_notes' => [661.1, 5],
        ];
        foreach ($sections as $field => [$firstLine, $lines]) {
            $note = trim((string) $observation?->{$field});
            if ($note !== '') {
                $items[] = [
                    'left' => 82, 'top' => $firstLine - 7.5, 'width' => 451, 'height' => $lines * 16,
                    'text' => $note, 'size' => 8, 'align' => 'left', 'lineHeight' => 16,
                ];
            }
        }

        return $items;
    }

    /** Page 18: raw and scaled scores per domain, sum, standard score and interpretation per evaluation. */
    private function summaryPage(array $computed): array
    {
        $rawColumns = [1 => [207.3, 263.4], 2 => [322.3, 379.3], 3 => [440.8, 502.6]];
        $scaledColumns = [1 => [263.4, 322.3], 2 => [379.3, 440.8], 3 => [502.6, 564.3]];
        $domainRows = [[191.1, 208.1], [208.1, 229.2], [229.2, 250.0], [250.0, 271.4], [271.4, 289.2], [289.2, 310.0], [310.0, 329.2]];
        $dateLefts = [1 => 295.0, 2 => 411.5, 3 => 529.5];
        $ageLines = [1 => [218.6, 308.6], 2 => [334.1, 424.1], 3 => [452.4, 542.4]];
        $items = [];

        foreach (self::ROUNDS as $round) {
            $result = $computed[$round];
            if ($result['evaluation_date'] === null) {
                continue;
            }

            $items[] = $this->text($dateLefts[$round], 145.2, 34, Carbon::parse($result['evaluation_date'])->format('m/d/y'), 5.5);
            if ($result['age']) {
                $items[] = $this->centered($ageLines[$round][0], $ageLines[$round][1], 152.5, 164.0, $this->ageText($result['age']), 7);
            }

            foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $index => $domainKey) {
                [$top, $bottom] = $domainRows[$index];
                $items[] = $this->centered($rawColumns[$round][0], $rawColumns[$round][1], $top, $bottom, $result['raw_scores'][$domainKey] ?? null, 9);
                $items[] = $this->centered($scaledColumns[$round][0], $scaledColumns[$round][1], $top, $bottom, $result['scaled_scores'][$domainKey] ?? null, 9);
            }

            $items[] = $this->centered($scaledColumns[$round][0], $scaledColumns[$round][1], 329.2, 349.8, $result['sum_scaled_score'], 9, true);
            $items[] = $this->centered($scaledColumns[$round][0], $scaledColumns[$round][1], 349.8, 371.2, $result['standard_score'], 9, true);
            if ($result['standard_score'] !== null) {
                $items[] = $this->centered($rawColumns[$round][0] + 3, $scaledColumns[$round][1] - 3, 371.2, 422.0, $result['interpretation']['overall'], 8, false, 3);
            }
        }

        return $items;
    }

    /** Page 19: "Child's Age" above each scaled-score panel. */
    private function scaledAgeLabels(array $computed): array
    {
        $items = [];
        foreach (self::SCALED_CHART as $round => $panel) {
            if ($computed[$round]['age'] ?? null) {
                $items[] = $this->centered($panel['age'][0], $panel['age'][1], 117, 128.5, $this->ageText($computed[$round]['age']), 6.2);
            }
        }

        return $items;
    }

    /** Page 20: "Date Tested" under the age column of each evaluation. */
    private function standardDates(array $computed): array
    {
        $datesByColumn = [];
        foreach ($this->standardPoints($computed) as $point) {
            $datesByColumn[$point['column']][] = $point['date'];
        }

        $items = [];
        foreach ($datesByColumn as $column => $dates) {
            [$left, , $right] = self::STANDARD_COLUMNS[$column];
            $items[] = $this->centered($left, $right, 730.5, 748.0, implode('   ', $dates), 7.5);
        }

        return $items;
    }

    /** Page 19 overlay: an x on each domain's scaled score, connected per evaluation. */
    private function scaledChartSvg(array $computed): ?string
    {
        $shapes = '';
        foreach (self::SCALED_CHART as $round => $panel) {
            $points = [];
            foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $index => $domainKey) {
                $scaled = $computed[$round]['scaled_scores'][$domainKey] ?? null;
                if ($scaled === null) {
                    continue;
                }
                $y = $panel['y19'] + (19 - max(1, min(19, $scaled))) * ($panel['y1'] - $panel['y19']) / 18;
                $points[] = [$panel['x'][$index], $y];
            }
            $shapes .= $this->connectedMarks($points);
        }

        return $this->svg($shapes);
    }

    /** Page 20 overlay: an x on each standard score in the column for the child's age, connected across evaluations. */
    private function standardChartSvg(array $computed): ?string
    {
        $points = array_map(fn (array $point) => [$point['x'], $point['y']], $this->standardPoints($computed));

        return $this->svg($this->connectedMarks($points));
    }

    /** @return list<array{x: float, y: float, column: int, date: string}> */
    private function standardPoints(array $computed): array
    {
        $byColumn = [];
        foreach (self::ROUNDS as $round) {
            $result = $computed[$round];
            $column = $result['age']['years'] ?? null;
            if ($result['standard_score'] === null || ! isset(self::STANDARD_COLUMNS[$column])) {
                continue;
            }
            $byColumn[$column][] = [
                'y' => $this->standardY($result['standard_score']),
                'date' => Carbon::parse($result['evaluation_date'])->format('m/d/Y'),
            ];
        }

        // Several evaluations in the same age column sit side by side around its ruler.
        $points = [];
        foreach ($byColumn as $column => $entries) {
            $count = count($entries);
            foreach ($entries as $index => $entry) {
                $points[] = $entry + [
                    'x' => self::STANDARD_COLUMNS[$column][1] + ($index - ($count - 1) / 2) * 28,
                    'column' => $column,
                ];
            }
        }

        return $points;
    }

    private function standardY(int $score): float
    {
        $score = max(20, min(160, $score));
        $upper = (int) (ceil($score / 10) * 10);
        $lower = (int) (floor($score / 10) * 10);
        if ($upper === $lower) {
            return self::STANDARD_AXIS[$score];
        }

        return self::STANDARD_AXIS[$lower] + ($score - $lower) / 10 * (self::STANDARD_AXIS[$upper] - self::STANDARD_AXIS[$lower]);
    }

    private function connectedMarks(array $points): string
    {
        if ($points === []) {
            return '';
        }

        $shapes = '';
        if (count($points) > 1) {
            $path = implode(' ', array_map(fn (array $p) => round($p[0], 1).','.round($p[1], 1), $points));
            $shapes .= '<polyline points="'.$path.'" fill="none" stroke="#c00000" stroke-width="0.9"/>';
        }
        foreach ($points as [$x, $y]) {
            $shapes .= sprintf(
                '<line x1="%1$.1f" y1="%2$.1f" x2="%3$.1f" y2="%4$.1f" stroke="#c00000" stroke-width="1.4"/><line x1="%1$.1f" y1="%4$.1f" x2="%3$.1f" y2="%2$.1f" stroke="#c00000" stroke-width="1.4"/>',
                $x - 3.5, $y - 3.5, $x + 3.5, $y + 3.5
            );
        }

        return $shapes;
    }

    private function svg(string $shapes): ?string
    {
        if ($shapes === '') {
            return null;
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d">%3$s</svg>',
            self::PAGE_WIDTH, self::PAGE_HEIGHT, $shapes
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /** One line of text sitting on a form underline (top = text box top). */
    private function text(float $left, float $top, float $width, mixed $value, float $size = 8, string $align = 'left'): array
    {
        return [
            'left' => $left, 'top' => $top, 'width' => $width, 'height' => $size * 1.4,
            'text' => filled($value) ? (string) $value : '', 'size' => $size, 'align' => $align, 'nowrap' => true,
        ];
    }

    /** Text centred in a cell. $lines > 1 allows wrapping (e.g. the interpretation cell). */
    private function centered(float $left, float $right, float $top, float $bottom, mixed $value, float $size, bool $bold = false, int $lines = 1): array
    {
        // 1.4 × the font size leaves room for descenders (y, g, p) inside the clipped box.
        $lineHeight = $size * 1.4;
        $height = $lineHeight * $lines;

        return [
            'left' => $left, 'top' => ($top + $bottom) / 2 - $height / 2, 'width' => $right - $left, 'height' => $height,
            'text' => filled($value) ? (string) $value : '', 'size' => $size, 'align' => 'center', 'bold' => $bold,
            'nowrap' => $lines === 1, 'lineHeight' => $lineHeight,
        ];
    }

    /** ✔ / — in a Present cell, centred on ($x, $y). */
    private function mark(float $x, float $y, string $mark): array
    {
        return $this->centered($x - 9, $x + 9, $y - 6, $y + 6, $mark, $mark === '✔' ? 10 : 9, true);
    }

    private function checkMark(float $x, float $y): array
    {
        return $this->centered($x - 6, $x + 6, $y - 6, $y + 6, '✔', 9, true);
    }

    private function ageText(array $age): string
    {
        return sprintf('%d yrs %d mos %d days', $age['years'], $age['months'], $age['days']);
    }

    private function ordinal(int $number): string
    {
        $suffix = in_array($number % 100, [11, 12, 13], true) ? 'th' : (['th', 'st', 'nd', 'rd'][$number % 10] ?? 'th');

        return $number.$suffix;
    }

    private function fullName($person): string
    {
        return $person
            ? trim(implode(' ', array_filter([$person->first_name, $person->middle_name, $person->last_name])))
            : '';
    }
}
