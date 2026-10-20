<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgressIndexRequest;
use App\Models\Domain;
use App\Models\DomainResult;
use App\Models\EccdChecklistItem;
use App\Models\ProgressRecord;
use App\Services\ChildsRecord2Report;
use App\Services\EccdExpertSystem;
use App\Support\Search;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProgressController extends Controller
{
    public function downloadPdf(ProgressRecord $progressRecord, ChildsRecord2Report $report)
    {
        $teacher = request()->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $progressRecord = $teacher->progressRecords()->findOrFail($progressRecord->id);

        $pdf = Pdf::loadView('progress.pdf.report', ['pages' => $report->build($progressRecord)])
            ->setPaper([0, 0, ChildsRecord2Report::PAGE_WIDTH, ChildsRecord2Report::PAGE_HEIGHT]);

        recordLog('downloaded', 'ProgressRecord', $progressRecord->id, 'ECCD progress report PDF downloaded.');

        return $pdf->download("ECCD-Checklist-Child-{$progressRecord->child_id}.pdf");
    }

    private function getDomainRoundStatus(int $childId, string $domainKey): array
    {
        $status = [1 => 'Pending', 2 => 'Pending', 3 => 'Pending'];

        $progressRecord = ProgressRecord::where('child_id', $childId)->first();
        if (! $progressRecord) {
            return $status;
        }

        $totalItems = EccdChecklistItem::ofType($domainKey)->count();
        if ($totalItems === 0) {
            return $status;
        }

        for ($round = 1; $round <= 3; $round++) {
            $domainItems = Domain::where('progress_record_id', $progressRecord->id)
                ->where('evaluation_round', $round)
                ->whereHas('checklistItem', fn ($q) => $q->where('domain', $domainKey))
                ->with('results')
                ->get();

            $answeredCount = $domainItems->filter(function ($domainItem) {
                return $domainItem->status === 'completed'
                    && $domainItem->results->contains(
                        fn ($result) => in_array($result->present, ['check', 'hyphen'], true)
                    );
            })->count();

            if ($domainItems->count() === $totalItems && $answeredCount === $totalItems) {
                $status[$round] = 'Completed';
            } elseif ($answeredCount > 0 || $domainItems->isNotEmpty()) {
                $status[$round] = 'In Progress';
            } else {
                $status[$round] = 'Pending';
            }
        }

        return $status;
    }

    private function getGlobalRoundCompleted(int $childId): array
    {
        $definedDomains = [
            'gross_motor', 'fine_motor', 'self_help',
            'receptive_language', 'expressive_language',
            'cognitive', 'social_emotional',
        ];

        $global = [1 => 0, 2 => 0, 3 => 0];

        foreach ($definedDomains as $key) {
            $statuses = $this->getDomainRoundStatus($childId, $key);
            foreach ([1, 2, 3] as $round) {
                if ($statuses[$round] === 'Completed') {
                    $global[$round]++;
                }
            }
        }

        return $global;
    }

    private function getMaxAllowedRound(int $childId): int
    {
        $global = $this->getGlobalRoundCompleted($childId);

        $max = 1;
        if ($global[1] >= 7) {
            $max = 2;
        }
        if ($global[2] >= 7) {
            $max = 3;
        }

        return $max;
    }

    private function resolveCurrentRound(array $statuses, int $maxAllowedRound): int
    {
        $completed = count(array_filter($statuses, fn (string $status) => $status === 'Completed'));

        return min($completed + 1, 3, $maxAllowedRound);
    }

    private function getOrCreateProgressRecord(int $childId, int $teacherId, string $assessmentDate): ProgressRecord
    {
        return ProgressRecord::firstOrCreate(
            ['child_id' => $childId],
            [
                'teacher_id' => $teacherId,
                'evaluation_date' => $assessmentDate,
                'evaluation_number' => 1,
                'status' => 'in_progress',
            ]
        );
    }

    public function index(ProgressIndexRequest $request)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $children = Search::apply($teacher->ownedChildren(), $request->search(), ['first_name', 'middle_name', 'last_name'])
            ->when($request->recordStatuses(), fn ($query, array $statuses) => $query->whereHas(
                'progressRecords',
                fn ($records) => $records->where('teacher_id', $teacher->id)->whereIn('status', $statuses)
            ))
            ->with(['progressRecords' => fn ($query) => $query->where('teacher_id', $teacher->id)->with(['domains.checklistItem', 'teacher'])])
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        $children->getCollection()->transform(function ($child) {
            $child->latest_record = $child->progressRecords->first();
            $progress = $this->currentRoundProgress($child->id, $child->latest_record);
            $child->current_evaluation_round = $progress['round'];
            $child->current_round_status = $progress['status'];
            $child->completed_domains_count = $progress['completed'];
            $child->current_round_date = $progress['date'];

            return $child;
        });

        return view('progress.index', compact('children'));
    }

    private function currentRoundProgress(int $childId, ?ProgressRecord $progressRecord): array
    {
        $roundStats = [
            1 => ['completed' => 0, 'pending' => 0],
            2 => ['completed' => 0, 'pending' => 0],
            3 => ['completed' => 0, 'pending' => 0],
        ];
        $currentRound = 1;

        foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $key) {
            foreach ($this->getDomainRoundStatus($childId, $key) as $round => $status) {
                if ($status === 'Pending') {
                    $roundStats[$round]['pending']++;

                    continue;
                }
                if ($status === 'Completed') {
                    $roundStats[$round]['completed']++;
                }
                $currentRound = max($currentRound, $round);
            }
        }

        $stats = $roundStats[$currentRound];
        $status = match (true) {
            $stats['completed'] >= 7 => 'completed',
            $stats['pending'] >= 7 => 'pending',
            default => 'in_progress',
        };
        $date = $progressRecord?->getAttribute("evaluation_date_round_{$currentRound}")
            ?? ($currentRound === 1 ? $progressRecord?->evaluation_date : null);

        return [
            'round' => $currentRound,
            'status' => $status,
            'completed' => $stats['completed'],
            'date' => $date ? Carbon::parse($date)->toDateString() : null,
        ];
    }

    /** Where a progress link without its child or record id lands: the Progress Records list, with a hint. */
    private function redirectToProgressIndex(string $message)
    {
        return redirect()->route('progress.index')->with('error', $message);
    }

    public function selectDomain(Request $request, $child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        if (! $childId) {
            return $this->redirectToProgressIndex('Select a child to evaluate.');
        }

        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $child = $teacher->ownedChildren()->findOrFail($childId);

        $definedDomains = [
            'gross_motor' => 'Gross Motor',
            'fine_motor' => 'Fine Motor',
            'self_help' => 'Self Help',
            'receptive_language' => 'Receptive Language',
            'expressive_language' => 'Expressive Language',
            'cognitive' => 'Cognitive',
            'social_emotional' => 'Social Emotional',
        ];

        $maxAllowedRound = $child ? $this->getMaxAllowedRound($child->id) : 1;

        $domains = [];

        foreach ($definedDomains as $key => $name) {
            $roundStatus = $child
                ? $this->getDomainRoundStatus($child->id, $key)
                : [1 => 'Pending', 2 => 'Pending', 3 => 'Pending'];

            $domains[] = [
                'key' => $key,
                'name' => $name,
                'round_status' => $roundStatus,
                'max_allowed_round' => $maxAllowedRound,
            ];
        }

        return view('progress.select-domain', compact('child', 'childId', 'domains', 'maxAllowedRound'));
    }

    public function create(Request $request, $child_id = null)
    {
        if (! $child_id) {
            return $this->redirectToProgressIndex('Select a child to evaluate.');
        }

        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $child = $teacher->ownedChildren()->findOrFail($child_id);
        $domainKey = $request->query('domain');

        $allowed = [
            'gross_motor', 'fine_motor', 'self_help',
            'receptive_language', 'expressive_language',
            'cognitive', 'social_emotional',
        ];

        if (! in_array($domainKey, $allowed)) {
            abort(404, 'Invalid domain');
        }

        $items = EccdChecklistItem::ofType($domainKey)
            ->orderBy('item_number')
            ->orderBy('id')
            ->get();

        $currentRound = 1;
        $completedRounds = [];
        $savedResults = [];
        $maxAllowedRound = 1;
        $progressRecord = null;

        if ($child) {
            $maxAllowedRound = $this->getMaxAllowedRound($child->id);
            $statuses = $this->getDomainRoundStatus($child->id, $domainKey);

            $progressRecord = $teacher->progressRecords()->where('child_id', $child->id)->first();

            foreach ([1, 2, 3] as $round) {
                if ($statuses[$round] === 'Completed') {
                    $completedRounds[] = $round;
                }

                $savedResults[$round] = [];

                if ($progressRecord) {
                    $domainItems = Domain::where('progress_record_id', $progressRecord->id)
                        ->where('evaluation_round', $round)
                        ->whereHas('checklistItem', fn ($q) => $q->where('domain', $domainKey))
                        ->with('results')
                        ->get();

                    foreach ($domainItems as $domainItem) {
                        $result = $domainItem->results->first();
                        if ($result) {
                            $savedResults[$round][$domainItem->checklist_item_id] = [
                                'present' => $result->present,
                                'comments' => $result->comments,
                            ];
                        }
                    }
                }
            }

            $currentRound = $this->resolveCurrentRound($statuses, $maxAllowedRound);
        }

        $assessmentDate = $progressRecord?->getAttribute("evaluation_date_round_{$currentRound}")
            ?? now()->toDateString();

        $domainNames = [
            'gross_motor' => 'Gross Motor',
            'fine_motor' => 'Fine Motor',
            'self_help' => 'Self-Help',
            'receptive_language' => 'Receptive Language',
            'expressive_language' => 'Expressive Language',
            'cognitive' => 'Cognitive',
            'social_emotional' => 'Social-Emotional',
        ];

        return view('progress.create', [
            'child' => $child,
            'domainKey' => $domainKey,
            'domainName' => $domainNames[$domainKey],
            'items' => $items,
            'currentRound' => $currentRound,
            'completedRounds' => $completedRounds,
            'savedResults' => $savedResults,
            'maxAllowedRound' => $maxAllowedRound,
            'assessmentDate' => $assessmentDate,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'domain' => 'required|in:gross_motor,fine_motor,self_help,receptive_language,expressive_language,cognitive,social_emotional',
            'assessment_date' => 'required|date|before_or_equal:today',
            'results' => 'nullable|array',
            'results.*.*' => [
                'nullable',
                Rule::in(['check', 'hyphen', 'present', '—', true, false, 1, 0, '1', '0']),
            ],
            'comments' => 'nullable|array',
            'comments.*' => 'nullable|string|max:5000',
        ]);

        $childId = (int) $validated['child_id'];
        $domainKey = $validated['domain'];
        $child = Auth::user()?->teacher?->ownedChildren()->findOrFail($childId);
        abort_unless($child, 403, 'A teacher profile is required.');
        $request->validate([
            'assessment_date' => 'after_or_equal:'.$child->date_of_birth,
        ]);
        $assessmentDate = $validated['assessment_date'];
        $results = $request->input('results', []);
        $comments = $request->input('comments', []);

        $teacher = Auth::user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');

        $statuses = $this->getDomainRoundStatus($childId, $domainKey);
        $completedRounds = [];
        foreach ([1, 2, 3] as $r) {
            if ($statuses[$r] === 'Completed') {
                $completedRounds[] = $r;
            }
        }

        $currentRound = min(count($completedRounds) + 1, 3);

        $maxAllowedRound = $this->getMaxAllowedRound($childId);
        if ($currentRound > $maxAllowedRound) {
            return back()->with('error', 'You cannot start this evaluation yet. Finish the previous evaluation for all 7 domains first.');
        }

        $responseErrors = [];
        foreach ($comments as $itemId => $comment) {
            $answer = $results[$itemId][$currentRound] ?? null;
            if (is_string($comment) && trim($comment) !== '' && ($answer === null || $answer === '')) {
                $responseErrors["comments.{$itemId}"] = 'Select a present or not-present response before saving this comment.';
            }
        }
        if ($responseErrors !== []) {
            return back()->withErrors($responseErrors)->withInput();
        }

        DB::beginTransaction();

        try {
            $progressRecord = $this->getOrCreateProgressRecord($childId, $teacher->id, $assessmentDate);

            $existingDomains = Domain::where('progress_record_id', $progressRecord->id)
                ->where('evaluation_round', $currentRound)
                ->whereHas('checklistItem', fn ($q) => $q->where('domain', $domainKey))
                ->with('results')
                ->get()
                ->keyBy('checklist_item_id');

            $masterItems = EccdChecklistItem::ofType($domainKey)
                ->orderBy('item_number')
                ->orderBy('id')
                ->get();

            $filledCount = 0;
            $totalItems = $masterItems->count();
            $expert = app(EccdExpertSystem::class);

            foreach ($masterItems as $master) {
                $roundResults = $results[$master->id] ?? [];
                $answerSubmitted = is_array($roundResults)
                    && array_key_exists($currentRound, $roundResults)
                    && $roundResults[$currentRound] !== null
                    && $roundResults[$currentRound] !== '';
                $presentValue = $answerSubmitted
                    ? $expert->normalizePresentValue($roundResults[$currentRound])
                    : null;
                $comment = $comments[$master->id] ?? null;
                $hasComment = is_string($comment) && trim($comment) !== '';

                if (isset($existingDomains[$master->id])) {
                    $domain = $existingDomains[$master->id];
                } else {
                    $domain = Domain::create([
                        'progress_record_id' => $progressRecord->id,
                        'checklist_item_id' => $master->id,
                        'evaluation_round' => $currentRound,
                        'status' => 'pending',
                    ]);
                }

                if ($presentValue !== null || $hasComment) {
                    $result = $domain->results()->first();
                    $resultData = [
                        'domain_id' => $domain->id,
                        'present' => $presentValue ?? 'hyphen',
                        'comments' => $hasComment ? $comment : null,
                    ];

                    if ($result) {
                        $result->update($resultData);
                    } else {
                        DomainResult::create($resultData);
                    }

                    $domain->update([
                        'status' => $presentValue !== null ? 'completed' : 'pending',
                    ]);

                    if ($presentValue !== null) {
                        $filledCount++;
                    }
                } else {
                    $domain->results()->delete();
                    $domain->update(['status' => 'pending']);
                }
            }

            $progressRecord->update([
                "evaluation_date_round_{$currentRound}" => $assessmentDate,
                'evaluation_date' => $assessmentDate,
            ]);

            $global = $this->getGlobalRoundCompleted($childId);

            if ($global[1] >= 7 && $global[2] >= 7 && $global[3] >= 7) {
                $progressRecord->update([
                    'status' => 'completed',
                    'evaluation_number' => 3,
                ]);
            } else {
                $progressRecord->update([
                    'status' => 'in_progress',
                    'evaluation_number' => $currentRound,
                ]);
            }

            $computed = $expert->compute($progressRecord, $currentRound);
            $expert->saveScores($progressRecord, $currentRound, $computed);

            recordLog(
                'saved',
                'ProgressRecord',
                $progressRecord->id,
                "Assessment results saved for domain {$domainKey}, evaluation {$currentRound}."
            );

            DB::commit();

            $message = $filledCount === $totalItems
                ? "Evaluation {$currentRound} completed and saved successfully!"
                : "Evaluation {$currentRound} saved successfully! ({$filledCount}/{$totalItems} items filled)";

            return redirect()
                ->route('progress.select-domain', ['child_id' => $childId])
                ->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to save evaluation. Please try again.');
        }
    }

    public function preview(Request $request, EccdExpertSystem $expert)
    {
        $validated = $request->validate([
            'child_id' => ['required', 'integer'],
            'domain' => ['required', Rule::in(array_keys(EccdExpertSystem::DOMAIN_LABELS))],
            'assessment_date' => ['nullable', 'date', 'before_or_equal:today'],
            'results' => ['nullable', 'array'],
            'results.*' => ['nullable', 'array'],
            'results.*.*' => ['nullable', Rule::in(['check', 'hyphen'])],
        ]);

        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $child = $teacher->ownedChildren()->findOrFail($validated['child_id']);
        $request->validate(['assessment_date' => ['nullable', 'after_or_equal:'.$child->date_of_birth]]);

        $domainKey = $validated['domain'];
        $round = $this->resolveCurrentRound(
            $this->getDomainRoundStatus($child->id, $domainKey),
            $this->getMaxAllowedRound($child->id)
        );

        $progressRecord = $teacher->progressRecords()->where('child_id', $child->id)->first();
        $answers = $progressRecord ? $expert->roundAnswers($progressRecord, $round) : [];

        foreach (EccdChecklistItem::ofType($domainKey)->pluck('id') as $itemId) {
            $answer = $validated['results'][$itemId][$round] ?? null;
            if ($answer === null || $answer === '') {
                unset($answers[(int) $itemId]);
            } else {
                $answers[(int) $itemId] = $answer;
            }
        }

        $explanation = $expert->explain(
            $expert->computeFromAnswers($child->date_of_birth, $validated['assessment_date'] ?? null, $answers),
            $answers
        );

        return response()->json([
            'round' => $round,
            'domain' => ['key' => $domainKey] + $explanation['domains'][$domainKey],
            'explanation' => $explanation,
        ]);
    }

    public function addObservation(Request $request, $child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        if (! $childId) {
            return $this->redirectToProgressIndex('Select a child before adding observation notes.');
        }

        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $child = $teacher->ownedChildren()->findOrFail($childId);
        $progressRecord = $teacher->progressRecords()->with(['domainObservation', 'teacher'])
            ->where('child_id', $child->id)
            ->first();

        return view('progress.create-observation', compact('child', 'progressRecord', 'teacher'));
    }

    public function edit(Request $request, ?int $id = null)
    {
        if ($id === null) {
            return $this->redirectToProgressIndex('Select a progress record to edit.');
        }

        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $progressRecord = $teacher->progressRecords()->with([
            'child.guardian',
            'teacher',
            'domainObservation',
            'domains.checklistItem',
            'domains.results',
        ])->findOrFail($id);

        $domainLabels = [
            'gross_motor' => 'Gross Motor',
            'fine_motor' => 'Fine Motor',
            'self_help' => 'Self-Help',
            'receptive_language' => 'Receptive Language',
            'expressive_language' => 'Expressive Language',
            'cognitive' => 'Cognitive',
            'social_emotional' => 'Social-Emotional',
        ];

        $itemsByDomain = EccdChecklistItem::query()
            ->whereIn('domain', array_keys($domainLabels))
            ->orderBy('item_number')
            ->orderBy('id')
            ->get()
            ->groupBy('domain');
        $savedResults = [];

        foreach ($progressRecord->domains as $domain) {
            $result = $domain->results->first();
            if (! $result || ! $domain->checklistItem) {
                continue;
            }

            $savedResults[$domain->evaluation_round][$domain->checklist_item_id] = [
                'present' => $result->present,
                'comments' => $result->comments,
            ];
        }

        $assessmentDates = [];
        foreach ([1, 2, 3] as $round) {
            $assessmentDates[$round] = $progressRecord->getAttribute("evaluation_date_round_{$round}")
                ?? ($round === 1 ? $progressRecord->evaluation_date : null);
        }

        return view('progress.edit', compact(
            'progressRecord',
            'domainLabels',
            'itemsByDomain',
            'savedResults',
            'assessmentDates'
        ));
    }

    public function update(Request $request, int $id, EccdExpertSystem $expert)
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $progressRecord = $teacher->progressRecords()->with('child')->findOrFail($id);
        $rounds = [1, 2, 3];
        $childDateOfBirth = $progressRecord->child?->date_of_birth;
        $dateRules = ['nullable', 'date', 'before_or_equal:today'];
        if ($childDateOfBirth !== null) {
            $dateRules[] = 'after_or_equal:'.$childDateOfBirth;
        }

        $validated = $request->validate([
            'assessment_dates' => ['required', 'array'],
            'assessment_dates.1' => array_merge(['required'], $dateRules),
            'assessment_dates.2' => $dateRules,
            'assessment_dates.3' => $dateRules,
            'results' => ['nullable', 'array'],
            'results.*.*' => ['nullable', Rule::in(['', 'check', 'hyphen'])],
            'comments' => ['nullable', 'array'],
            'comments.*.*' => ['nullable', 'string', 'max:5000'],
            'observation' => ['nullable', 'array'],
            'observation.place_administered' => ['nullable', 'string', 'max:255'],
            'observation.background_observations' => ['nullable', 'string', 'max:5000'],
            'observation.family_environment_notes' => ['nullable', 'string', 'max:5000'],
            'observation.stimulating_activities_notes' => ['nullable', 'string', 'max:5000'],
            'observation.home_environment_notes' => ['nullable', 'string', 'max:5000'],
            'observation.other_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $responseErrors = [];
        foreach ($validated['comments'] ?? [] as $itemId => $roundComments) {
            foreach ($roundComments as $round => $comment) {
                $answer = $validated['results'][$itemId][$round] ?? '';
                if (trim((string) $comment) !== '' && $answer === '') {
                    $responseErrors["comments.{$itemId}.{$round}"] = 'Select a present or not-present response before saving this comment.';
                }
            }
        }
        if ($responseErrors !== []) {
            return back()->withErrors($responseErrors)->withInput();
        }

        $validItemIds = EccdChecklistItem::query()
            ->whereIn('domain', [
                'gross_motor', 'fine_motor', 'self_help',
                'receptive_language', 'expressive_language',
                'cognitive', 'social_emotional',
            ])
            ->pluck('id')
            ->map(fn ($itemId) => (int) $itemId)
            ->all();

        DB::transaction(function () use ($progressRecord, $validated, $validItemIds, $rounds, $expert) {
            foreach ($rounds as $round) {
                $date = $validated['assessment_dates'][$round] ?? null;
                $progressRecord->setAttribute("evaluation_date_round_{$round}", $date);

                foreach ($validItemIds as $itemId) {
                    $answer = $validated['results'][$itemId][$round] ?? '';
                    $comment = trim((string) ($validated['comments'][$itemId][$round] ?? ''));
                    $domain = Domain::firstOrNew([
                        'progress_record_id' => $progressRecord->id,
                        'checklist_item_id' => $itemId,
                        'evaluation_round' => $round,
                    ]);

                    if (! $domain->exists && $answer === '' && $comment === '') {
                        continue;
                    }

                    if (! $domain->exists) {
                        $domain->status = 'pending';
                        $domain->save();
                    }

                    if ($answer === '') {
                        $domain->results()->delete();
                        $domain->update(['status' => 'pending']);

                        continue;
                    }

                    $domain->results()->updateOrCreate(
                        [],
                        [
                            'present' => $answer,
                            'comments' => $comment !== '' ? $comment : null,
                        ]
                    );
                    $domain->update(['status' => 'completed']);
                }
            }

            $progressRecord->evaluation_date = $validated['assessment_dates'][1] ?? null;
            $global = $this->getGlobalRoundCompleted($progressRecord->child_id);
            $allRoundsComplete = $global[1] >= 7 && $global[2] >= 7 && $global[3] >= 7;
            $progressRecord->status = $allRoundsComplete ? 'completed' : 'in_progress';
            $progressRecord->evaluation_number = $allRoundsComplete
                ? 3
                : $this->getMaxAllowedRound($progressRecord->child_id);
            $progressRecord->save();

            $progressRecord->load('child');
            foreach ($rounds as $round) {
                $computed = $expert->compute($progressRecord, $round);
                $expert->saveScores($progressRecord, $round, $computed);
            }

            $observation = $validated['observation'] ?? [];
            $progressRecord->domainObservation()->updateOrCreate(
                [],
                [
                    'place_administered' => $observation['place_administered'] ?? null,
                    'background_observations' => $observation['background_observations'] ?? null,
                    'family_environment_notes' => $observation['family_environment_notes'] ?? null,
                    'stimulating_activities_notes' => $observation['stimulating_activities_notes'] ?? null,
                    'home_environment_notes' => $observation['home_environment_notes'] ?? null,
                    'other_notes' => $observation['other_notes'] ?? null,
                    'status' => 'completed',
                ]
            );

            recordLog('updated', 'ProgressRecord', $progressRecord->id, 'Assessment results and observation notes updated.');
        });

        return redirect()
            ->route('progress.show', $progressRecord->id)
            ->with('success', 'Progress record updated successfully.');
    }

    public function storeObservation(Request $request)
    {
        $validated = $request->validate([
            'child_id' => ['required', 'integer', 'exists:children,id'],
            'date_administered' => ['required', 'date', 'before_or_equal:today'],
            'place_administered' => ['nullable', 'string', 'max:255'],
            'background_observations' => ['nullable', 'string', 'max:5000'],
            'family_environment_notes' => ['nullable', 'string', 'max:5000'],
            'stimulating_activities_notes' => ['nullable', 'string', 'max:5000'],
            'home_environment_notes' => ['nullable', 'string', 'max:5000'],
            'other_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        $child = $teacher->ownedChildren()->findOrFail($validated['child_id']);

        DB::transaction(function () use ($validated, $child, $teacher) {
            $progressRecord = ProgressRecord::firstOrCreate(
                ['child_id' => $child->id],
                [
                    'teacher_id' => $teacher->id,
                    'evaluation_date' => $validated['date_administered'],
                    'evaluation_number' => 1,
                    'status' => 'in_progress',
                ]
            );

            $progressRecord->update([
                'evaluation_date' => $validated['date_administered'],
            ]);

            $progressRecord->domainObservation()->updateOrCreate(
                [],
                [
                    'place_administered' => $validated['place_administered'] ?? null,
                    'background_observations' => $validated['background_observations'] ?? null,
                    'family_environment_notes' => $validated['family_environment_notes'] ?? null,
                    'stimulating_activities_notes' => $validated['stimulating_activities_notes'] ?? null,
                    'home_environment_notes' => $validated['home_environment_notes'] ?? null,
                    'other_notes' => $validated['other_notes'] ?? null,
                    'status' => 'completed',
                ]
            );

            recordLog('saved', 'ProgressRecord', $progressRecord->id, 'Observation notes saved.');
        });

        return redirect()
            ->route('progress.add-observation', ['child_id' => $child->id])
            ->with('success', 'Observation notes saved successfully.');
    }

    public function show(Request $request, $id = null)
    {
        if ($id === null) {
            return $this->redirectToProgressIndex('Select a progress record to view.');
        }

        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');
        // The id is always a progress record id; links never pass a child id here.
        $progressRecord = $teacher->progressRecords()->with([
            'child',
            'teacher',
            'domains.checklistItem',
            'domains.results',
            'domainScores',
            'domainObservation',
        ])->findOrFail($id);
        $child = $progressRecord->child;
        recordLog('viewed', 'ProgressRecord', $progressRecord->id, 'Progress record viewed.');

        $domainKeys = [
            'gross_motor' => 'Gross Motor',
            'fine_motor' => 'Fine Motor',
            'self_help' => 'Self-Help',
            'receptive_language' => 'Receptive Language',
            'expressive_language' => 'Expressive Language',
            'cognitive' => 'Cognitive',
            'social_emotional' => 'Social-Emotional',
        ];

        $preparedDomains = [];
        $rawScoresByRound = [1 => [], 2 => [], 3 => []];
        $computedByRound = [1 => null, 2 => null, 3 => null];
        $ageByRound = [1 => null, 2 => null, 3 => null];
        $assessmentDates = [1 => null, 2 => null, 3 => null];
        $expert = app(EccdExpertSystem::class);

        if ($progressRecord) {
            foreach ([1, 2, 3] as $round) {
                $assessmentDates[$round] = $progressRecord->getAttribute("evaluation_date_round_{$round}")
                    ?? ($round === 1 ? $progressRecord->evaluation_date : null);
            }

            $allDomains = $progressRecord->domains;

            foreach ($domainKeys as $key => $name) {
                $masterItems = EccdChecklistItem::ofType($key)
                    ->orderBy('item_number')
                    ->orderBy('id')
                    ->get();

                $items = [];

                foreach ($masterItems as $master) {
                    $vals = [null, null, null];
                    $commentsByRound = [1 => null, 2 => null, 3 => null];

                    for ($r = 1; $r <= 3; $r++) {
                        $domainEntry = $allDomains->first(function ($d) use ($master, $r) {
                            return $d->checklist_item_id == $master->id
                                && $d->evaluation_round == $r;
                        });

                        $result = $domainEntry?->results->first();
                        if ($result) {
                            $vals[$r - 1] = $expert->normalizePresentValue($result->present);
                            $comment = trim((string) $result->comments);
                            if ($comment !== '') {
                                $commentsByRound[$r] = $comment;
                            }
                        }
                    }

                    $roundLabels = [1 => '1st', 2 => '2nd', 3 => '3rd'];
                    $itemComments = [];
                    foreach ($commentsByRound as $round => $comment) {
                        if ($comment !== null) {
                            $itemComments[] = "{$roundLabels[$round]}: {$comment}";
                        }
                    }

                    $items[] = [
                        'id' => $master->item_number ?? $master->id,
                        'task' => $master->activity,
                        'proc' => $master->materials_and_procedure ?? 'Parental report will suffice.',
                        'val' => $vals,
                        'comments' => implode(' | ', $itemComments),
                    ];
                }

                $preparedDomains[$key] = [
                    'name' => $name,
                    'items' => $items,
                ];
            }

            foreach ([1, 2, 3] as $round) {
                $rawScoresByRound[$round] = $expert->computeRawScores($progressRecord, $round);
            }

            foreach ([1, 2, 3] as $round) {
                $computedByRound[$round] = $expert->compute($progressRecord, $round);
                $ageByRound[$round] = $computedByRound[$round]['age'];
            }
        }

        $explanations = [];
        foreach ([1, 2, 3] as $round) {
            if (! empty($assessmentDates[$round])) {
                $explanations[$round] = $expert->explainRound($progressRecord, $round);
            }
        }
        $skillsToPractice = $expert->skillsToPractice($progressRecord);

        $computed = $computedByRound[1];
        $roundProgress = $this->currentRoundProgress($progressRecord->child_id, $progressRecord);
        $currentRound = $roundProgress['round'];

        return view('progress.show', compact(
            'roundProgress',
            'progressRecord',
            'child',
            'id',
            'preparedDomains',
            'rawScoresByRound',
            'computed',
            'computedByRound',
            'currentRound',
            'ageByRound',
            'assessmentDates',
            'explanations',
            'skillsToPractice'
        ));
    }
}
