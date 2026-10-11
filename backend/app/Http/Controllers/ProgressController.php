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
    /**
     * Status of each round for one domain of one child
     * Returns [1 => 'Pending'|'In Progress'|'Completed', 2 => ..., 3 => ...]
     */
    private function getDomainRoundStatus(int $childId, string $domainKey): array
    {
        $status = [1 => 'Pending', 2 => 'Pending', 3 => 'Pending'];

        $progressRecord = ProgressRecord::where('child_id', $childId)->first();
        if (!$progressRecord) {
            return $status;
        }

        for ($round = 1; $round <= 3; $round++) {
            $domainItems = Domain::where('progress_record_id', $progressRecord->id)
                ->where('evaluation_round', $round)
                ->whereHas('checklistItem', fn($q) => $q->where('domain', $domainKey))
                ->with('results')
                ->get();

            $totalItems  = $domainItems->count();
            $filledCount = $domainItems->filter(fn($d) => $d->results->isNotEmpty())->count();

            if ($totalItems === 0) {
                $status[$round] = 'Pending';
            } elseif ($filledCount === $totalItems) {
                $status[$round] = 'Completed';
            } elseif ($filledCount > 0) {
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
            'cognitive', 'social_emotional'
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
        if ($global[1] >= 7) $max = 2;
        if ($global[2] >= 7) $max = 3;

        return $max;
    }

    /**
     * ONE ProgressRecord per child
     */
    private function getOrCreateProgressRecord(int $childId, int $teacherId): ProgressRecord
    {
        return ProgressRecord::firstOrCreate(
            ['child_id' => $childId],
            [
                'teacher_id'        => $teacherId,
                'evaluation_date'   => now()->toDateString(),
                'evaluation_number' => 1,
                'status'            => 'in_progress',
            ]
        );
    }

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index()
    {
        $children = Child::with(['progressRecords.domains.checklistItem', 'progressRecords.teacher'])
            ->orderBy('first_name')
            ->paginate(10);

        $definedDomains = [
            'gross_motor', 'fine_motor', 'self_help',
            'receptive_language', 'expressive_language',
            'cognitive', 'social_emotional'
        ];

        $children->getCollection()->transform(function ($child) use ($definedDomains) {

            $roundStats = [
                1 => ['completed' => 0, 'in_progress' => 0, 'pending' => 0],
                2 => ['completed' => 0, 'in_progress' => 0, 'pending' => 0],
                3 => ['completed' => 0, 'in_progress' => 0, 'pending' => 0],
            ];

            $highestRoundWithRecord = 0;

            foreach ($definedDomains as $key) {
                $statuses = $this->getDomainRoundStatus($child->id, $key);

                foreach ([1, 2, 3] as $round) {
                    if ($statuses[$round] === 'Completed') {
                        $roundStats[$round]['completed']++;
                        if ($round > $highestRoundWithRecord) $highestRoundWithRecord = $round;
                    } elseif ($statuses[$round] === 'In Progress') {
                        $roundStats[$round]['in_progress']++;
                        if ($round > $highestRoundWithRecord) $highestRoundWithRecord = $round;
                    } else {
                        $roundStats[$round]['pending']++;
                    }
                }
            }

            $currentRound = $highestRoundWithRecord > 0 ? $highestRoundWithRecord : 1;
            $child->current_evaluation_round = $currentRound;

            $stats = $roundStats[$currentRound];

            if ($stats['completed'] >= 7) {
                $child->current_round_status = 'completed';
            } elseif ($stats['pending'] >= 7) {
                $child->current_round_status = 'pending';
            } else {
                $child->current_round_status = 'in_progress';
            }

            $child->completed_domains_count = $stats['completed'];
            $child->latest_record = $child->progressRecords->first();

            return $child;
        });

        return view('progress.index', compact('children'));
    }

    // =========================================================================
    // SELECT DOMAIN
    // =========================================================================
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
            $roundStatus = $child
                ? $this->getDomainRoundStatus($child->id, $key)
                : [1 => 'Pending', 2 => 'Pending', 3 => 'Pending'];

            $domains[] = [
                'key'               => $key,
                'name'              => $name,
                'round_status'      => $roundStatus,
                'max_allowed_round' => $maxAllowedRound,
            ];
        }

        return view('progress.select-domain', compact('child', 'childId', 'domains', 'maxAllowedRound'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================
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

        $items = EccdChecklistItem::ofType($domainKey)
            ->orderBy('item_number')
            ->orderBy('id')
            ->get();

        $currentRound    = 1;
        $completedRounds = [];
        $savedResults    = [];
        $maxAllowedRound = 1;

        if ($child) {
            $maxAllowedRound = $this->getMaxAllowedRound($child->id);
            $statuses = $this->getDomainRoundStatus($child->id, $domainKey);

            $progressRecord = ProgressRecord::where('child_id', $child->id)->first();

            foreach ([1, 2, 3] as $round) {
                if ($statuses[$round] === 'Completed') {
                    $completedRounds[] = $round;
                }

                $savedResults[$round] = [];

                if ($progressRecord) {
                    $domainItems = Domain::where('progress_record_id', $progressRecord->id)
                        ->where('evaluation_round', $round)
                        ->whereHas('checklistItem', fn($q) => $q->where('domain', $domainKey))
                        ->with('results')
                        ->get();

                    foreach ($domainItems as $domainItem) {
                        $result = $domainItem->results->first();
                        if ($result) {
                            $savedResults[$round][$domainItem->checklist_item_id] = [
                                'present'  => $result->present,
                                'comments' => $result->comments,
                            ];
                        }
                    }
                }
            }

            $currentRound = count($completedRounds) + 1;
            if ($currentRound > 3) $currentRound = 3;

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

    // =========================================================================
    // STORE  (FIXED)
    // =========================================================================
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
            // Determine current round for this domain
            $statuses = $this->getDomainRoundStatus($childId, $domainKey);
            $completedRounds = [];
            foreach ([1, 2, 3] as $r) {
                if ($statuses[$r] === 'Completed') $completedRounds[] = $r;
            }

            $currentRound = count($completedRounds) + 1;
            if ($currentRound > 3) $currentRound = 3;

            // Global lock
            $maxAllowedRound = $this->getMaxAllowedRound($childId);
            if ($currentRound > $maxAllowedRound) {
                return back()->with('error', 'You cannot start this evaluation round yet. Finish the previous round for all 7 domains first.');
            }

            // ===== ONE ProgressRecord per child =====
            $progressRecord = $this->getOrCreateProgressRecord($childId, $teacher->id);

            // Existing domain rows for this domain + this round
            $existingDomains = Domain::where('progress_record_id', $progressRecord->id)
                ->where('evaluation_round', $currentRound)
                ->whereHas('checklistItem', fn($q) => $q->where('domain', $domainKey))
                ->with('results')
                ->get()
                ->keyBy('checklist_item_id');

            $masterItems = EccdChecklistItem::ofType($domainKey)
                ->orderBy('item_number')
                ->orderBy('id')
                ->get();

            $filledCount = 0;
            $totalItems  = $masterItems->count();

            foreach ($masterItems as $master) {
                $presentValue = $results[$master->id][$currentRound] ?? null;

                if (isset($existingDomains[$master->id])) {
                    $domain = $existingDomains[$master->id];
                    $domain->results()->delete();
                } else {
                    $domain = Domain::create([
                        'progress_record_id' => $progressRecord->id,
                        'checklist_item_id'  => $master->id,
                        'evaluation_round'   => $currentRound,
                        'status'             => 'pending',
                    ]);
                }

                if ($presentValue) {
                    DomainResult::create([
                        'domain_id' => $domain->id,
                        'present'   => $presentValue,
                        'comments'  => $comments[$master->id] ?? null,
                    ]);

                    // Mark this item as completed
                    $domain->update(['status' => 'completed']);
                    $filledCount++;
                } else {
                    // No answer → pending
                    $domain->update(['status' => 'pending']);
                }
            }

            // Update ProgressRecord overall status
            $global = $this->getGlobalRoundCompleted($childId);

            if ($global[1] >= 7 && $global[2] >= 7 && $global[3] >= 7) {
                $progressRecord->update([
                    'status'            => 'completed',
                    'evaluation_number' => 3,
                    'evaluation_date'   => now()->toDateString(),
                ]);
            } else {
                $progressRecord->update([
                    'status'            => 'in_progress',
                    'evaluation_number' => $currentRound,
                    'evaluation_date'   => now()->toDateString(),
                ]);
            }

            DB::commit();

            $message = $filledCount === $totalItems
                ? "Evaluation (Round {$currentRound}) completed and saved successfully!"
                : "Evaluation (Round {$currentRound}) saved successfully! ({$filledCount}/{$totalItems} items filled)";

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