<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Domain;
use App\Models\DomainResult;
use App\Models\EccdChecklistItem;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    private function getGlobalRoundCompleted($childId)
    {
        $definedDomains = [
            'gross_motor', 'fine_motor', 'self_help',
            'receptive_language', 'expressive_language',
            'cognitive', 'social_emotional'
        ];

        $globalRoundCompleted = [1 => 0, 2 => 0, 3 => 0];

        foreach ($definedDomains as $key) {
            $progressRecords = ProgressRecord::where('child_id', $childId)
                ->whereHas('domains.checklistItem', fn($q) => $q->where('domain', $key))
                ->with(['domains' => fn($q) => $q->whereHas('checklistItem', fn($q2) => $q2->where('domain', $key))->with('results')])
                ->orderBy('evaluation_number')
                ->get();

            foreach ($progressRecords->values() as $index => $record) {
                $round = $index + 1;
                if ($round > 3) break;

                $domainItems = $record->domains;
                $totalItems  = $domainItems->count();
                $filledCount = $domainItems->filter(fn($d) => $d->results->isNotEmpty())->count();

                if ($totalItems > 0 && $filledCount === $totalItems) {
                    $globalRoundCompleted[$round]++;
                }
            }
        }

        return $globalRoundCompleted;
    }

    private function getMaxAllowedRound($childId)
    {
        $global = $this->getGlobalRoundCompleted($childId);

        $maxAllowedRound = 1;
        if ($global[1] >= 7) $maxAllowedRound = 2;
        if ($global[2] >= 7) $maxAllowedRound = 3;

        return $maxAllowedRound;
    }

    public function index()
    {
        $children = Child::with(['progressRecords.domains.checklistItem'])
            ->orderBy('first_name')
            ->paginate(10);

        $children->getCollection()->transform(function ($child) {
            $completedDomains = $child->progressRecords
                ->flatMap(fn($record) => $record->domains->pluck('checklistItem.domain'))
                ->unique()
                ->values();

            $totalCompleted = $completedDomains->count();

            if ($totalCompleted === 0) {
                $child->overall_status = 'pending';
            } elseif ($totalCompleted >= 7) {
                $child->overall_status = 'completed';
            } else {
                $child->overall_status = 'in_progress';
            }

            $child->completed_domains_count = $totalCompleted;
            $child->latest_record = $child->progressRecords
                ->sortByDesc('evaluation_number')
                ->first();

            return $child;
        });

        return view('progress.index', compact('children'));
    }

    public function selectDomain($child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        $child   = $childId ? Child::findOrFail($childId) : null;

        $definedDomains = [
            'gross_motor'         => 'Gross Motor',
            'fine_motor'          => 'Fine Motor',
            'self_help'           => 'Self Help',
            'receptive_language'  => 'Receptive Language',
            'expressive_language' => 'Expressive Language',
            'cognitive'           => 'Cognitive',
            'social_emotional'    => 'Social Emotional',
        ];

        $maxAllowedRound = $child ? $this->getMaxAllowedRound($child->id) : 1;

        $domains = [];

        foreach ($definedDomains as $key => $name) {
            $roundStatus = [1 => 'Pending', 2 => 'Pending', 3 => 'Pending'];

            if ($child) {
                $progressRecords = ProgressRecord::where('child_id', $child->id)
                    ->whereHas('domains.checklistItem', fn($q) => $q->where('domain', $key))
                    ->with(['domains' => fn($q) => $q->whereHas('checklistItem', fn($q2) => $q2->where('domain', $key))->with('results')])
                    ->orderBy('evaluation_number')
                    ->get();

                foreach ($progressRecords->values() as $index => $record) {
                    $round = $index + 1;
                    if ($round > 3) break;

                    $domainItems = $record->domains;
                    $totalItems  = $domainItems->count();
                    $filledCount = $domainItems->filter(fn($d) => $d->results->isNotEmpty())->count();

                    if ($totalItems === 0) {
                        $roundStatus[$round] = 'Pending';
                    } elseif ($filledCount === $totalItems) {
                        $roundStatus[$round] = 'Completed';
                    } elseif ($filledCount > 0) {
                        $roundStatus[$round] = 'In Progress';
                    } else {
                        $roundStatus[$round] = 'Pending';
                    }
                }
            }

            $domains[] = [
                'key'               => $key,
                'name'              => $name,
                'round_status'      => $roundStatus,
                'max_allowed_round' => $maxAllowedRound,
            ];
        }

        return view('progress.select-domain', compact('child', 'childId', 'domains', 'maxAllowedRound'));
    }

    public function create(Request $request, $child_id = null)
    {
        $child     = $child_id ? Child::findOrFail($child_id) : null;
        $domainKey = $request->query('domain');

        $allowed = [
            'gross_motor', 'fine_motor', 'self_help',
            'receptive_language', 'expressive_language',
            'cognitive', 'social_emotional'
        ];

        if (!in_array($domainKey, $allowed)) {
            abort(404, 'Invalid domain');
        }

        // Load master checklist items
        $items = EccdChecklistItem::ofType($domainKey)
            ->orderBy('item_number')
            ->orderBy('id')
            ->get();

        $currentRound    = 1;
        $completedRounds = [];
        $savedResults    = []; // [round => [checklist_item_id => ['present' => ..., 'comments' => ...]]]
        $maxAllowedRound = 1;

        if ($child) {
            $maxAllowedRound = $this->getMaxAllowedRound($child->id);

            $progressRecords = ProgressRecord::where('child_id', $child->id)
                ->whereHas('domains.checklistItem', fn($q) => $q->where('domain', $domainKey))
                ->with(['domains' => fn($q) => $q->whereHas('checklistItem', fn($q2) => $q2->where('domain', $domainKey))
                    ->with(['results', 'checklistItem'])])
                ->orderBy('evaluation_number')
                ->get();

            foreach ($progressRecords->values() as $index => $record) {
                $round = $index + 1;
                if ($round > 3) break;

                $domainItems = $record->domains;
                $totalItems  = $domainItems->count();
                $filledCount = $domainItems->filter(fn($d) => $d->results->isNotEmpty())->count();

                // Load answers for THIS round (whether completed or incomplete)
                $savedResults[$round] = [];
                foreach ($domainItems as $domainItem) {
                    $result = $domainItem->results->first();
                    if ($result) {
                        $savedResults[$round][$domainItem->checklist_item_id] = [
                            'present'  => $result->present,
                            'comments' => $result->comments,
                        ];
                    }
                }

                if ($totalItems > 0 && $filledCount === $totalItems) {
                    $completedRounds[] = $round;
                }
            }

            $currentRound = count($completedRounds) + 1;
            if ($currentRound > 3) $currentRound = 3;

            // Enforce global lock
            if ($currentRound > $maxAllowedRound) {
                $currentRound = $maxAllowedRound;
            }
        }

        $domainNames = [
            'gross_motor'          => 'Gross Motor',
            'fine_motor'           => 'Fine Motor',
            'self_help'            => 'Self-Help',
            'receptive_language'   => 'Receptive Language',
            'expressive_language'  => 'Expressive Language',
            'cognitive'            => 'Cognitive',
            'social_emotional'     => 'Social-Emotional',
        ];

        return view('progress.create', [
            'child'           => $child,
            'domainKey'       => $domainKey,
            'domainName'      => $domainNames[$domainKey],
            'items'           => $items,
            'currentRound'    => $currentRound,
            'completedRounds' => $completedRounds,
            'savedResults'    => $savedResults,
            'maxAllowedRound' => $maxAllowedRound,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'child_id' => 'required|exists:children,id',
            'domain'   => 'required|in:gross_motor,fine_motor,self_help,receptive_language,expressive_language,cognitive,social_emotional',
            'results'  => 'nullable|array',
            'comments' => 'nullable|array',
        ]);

        $childId   = $request->child_id;
        $domainKey = $request->domain;
        $results   = $request->input('results', []);
        $comments  = $request->input('comments', []);

        $teacher = Teacher::where('user_id', Auth::id())->firstOrFail();

        DB::beginTransaction();

        try {
            $existingRecords = ProgressRecord::where('child_id', $childId)
                ->whereHas('domains.checklistItem', fn($q) => $q->where('domain', $domainKey))
                ->with(['domains' => fn($q) => $q->whereHas('checklistItem', fn($q2) => $q2->where('domain', $domainKey))->with('results')])
                ->orderBy('evaluation_number')
                ->get();

            $incompleteRecord = null;

            foreach ($existingRecords as $record) {
                $domainItems = $record->domains;
                $totalItems  = $domainItems->count();
                $filledCount = $domainItems->filter(fn($d) => $d->results->isNotEmpty())->count();

                if ($totalItems > 0 && $filledCount < $totalItems) {
                    $incompleteRecord = $record;
                    break;
                }
            }

            if ($incompleteRecord) {
                // ========== UPDATE existing incomplete evaluation ==========
                $progressRecord = $incompleteRecord;

                foreach ($progressRecord->domains as $domain) {
                    $domain->results()->delete();
                }

                foreach ($progressRecord->domains as $domain) {
                    $checklistItemId = $domain->checklist_item_id;

                    $presentValue = null;
                    foreach ([1, 2, 3] as $round) {
                        if (isset($results[$checklistItemId][$round])) {
                            $presentValue = $results[$checklistItemId][$round];
                            break;
                        }
                    }

                    if ($presentValue) {
                        DomainResult::create([
                            'domain_id' => $domain->id,
                            'present'   => $presentValue,
                            'comments'  => $comments[$checklistItemId] ?? null,
                        ]);
                    }
                }

                $message = 'Evaluation updated successfully!';

            } else {
                // ========== CREATE new evaluation round ==========
                $maxAllowedRound = $this->getMaxAllowedRound($childId);
                $nextRound = $existingRecords->count() + 1;

                if ($nextRound > $maxAllowedRound) {
                    return back()->with('error', 'You cannot start this evaluation round yet. Finish the previous round for all 7 domains first.');
                }

                $nextEvalNumber = ProgressRecord::where('child_id', $childId)->max('evaluation_number') + 1;
                if ($nextEvalNumber < 1) $nextEvalNumber = 1;

                $progressRecord = ProgressRecord::create([
                    'child_id'          => $childId,
                    'teacher_id'        => $teacher->id,
                    'evaluation_date'   => now()->toDateString(),
                    'evaluation_number' => $nextEvalNumber,
                    'status'            => 'completed',
                ]);

                $masterItems = EccdChecklistItem::ofType($domainKey)->orderBy('item_number')->orderBy('id')->get();

                foreach ($masterItems as $master) {
                    $domain = Domain::create([
                        'progress_record_id' => $progressRecord->id,
                        'checklist_item_id'  => $master->id,
                        'status'             => 'completed',
                    ]);

                    $presentValue = null;
                    foreach ([1, 2, 3] as $round) {
                        if (isset($results[$master->id][$round])) {
                            $presentValue = $results[$master->id][$round];
                            break;
                        }
                    }

                    if ($presentValue) {
                        DomainResult::create([
                            'domain_id' => $domain->id,
                            'present'   => $presentValue,
                            'comments'  => $comments[$master->id] ?? null,
                        ]);
                    }
                }

                $message = 'Evaluation saved successfully!';
            }

            DB::commit();

            return redirect()
                ->route('progress.select-domain', ['child_id' => $childId])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to save evaluation: ' . $e->getMessage());
        }
    }

    public function addObservation($child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        $child   = $childId ? Child::find($childId) : null;
        return view('progress.create-observation', compact('child', 'childId'));
    }

    public function show($id = null)
    {
        $progressRecord = ProgressRecord::with([
            'child', 'teacher', 'domains.checklistItem', 'domains.results', 'domainScores', 'domainObservation'
        ])->find($id);

        if (!$progressRecord) {
            $progressRecord = ProgressRecord::with([
                'child', 'teacher', 'domains.checklistItem', 'domains.results', 'domainScores', 'domainObservation'
            ])
            ->where('child_id', $id)
            ->orderByDesc('evaluation_number')
            ->first();
        }

        $child = $progressRecord?->child ?? Child::find($id);
        return view('progress.show', compact('progressRecord', 'child', 'id'));
    }

    public function edit($id = null)
    {
        $progressRecord = $id ? ProgressRecord::with(['child', 'teacher'])->find($id) : null;
        return view('progress.edit', compact('progressRecord', 'id'));
    }
}