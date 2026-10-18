<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\EccdChecklistItem;
use App\Models\ProgressRecord;
use App\Services\EccdExpertSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GuardianController extends Controller
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

    private const ROUNDS = [1, 2, 3];

    // GET /guardian/profile
    public function profile(Request $request)
    {
        $guardian = $request->user()->guardian;

        return response()->json([
            'id' => $guardian->id,
            'first_name' => $guardian->first_name,
            'middle_name' => $guardian->middle_name,
            'last_name' => $guardian->last_name,
            'email' => $request->user()->email,
            'sex' => $guardian->sex,
            'contact_number' => $guardian->contact_number,
            'relationship_to_child' => $guardian->relationship_to_child,
            // Barangay, municipality, province and region are stored joined in `address`.
            'address' => $guardian->address,
        ], 200);
    }

    // GET /guardian/contacts
    // The teacher handling each of the guardian's children: name and contact number only.
    public function contacts(Request $request)
    {
        $children = $request->user()->guardian->children()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name']);

        return response()->json($children->map(function (Child $child) {
            $teacher = $this->latestProgressRecord($child)?->teacher;

            return [
                'child' => [
                    'id' => $child->id,
                    'first_name' => $child->first_name,
                    'middle_name' => $child->middle_name,
                    'last_name' => $child->last_name,
                ],
                'teacher' => $teacher ? [
                    'first_name' => $teacher->first_name,
                    'middle_name' => $teacher->middle_name,
                    'last_name' => $teacher->last_name,
                    'contact_number' => $teacher->contact_number,
                ] : null,
            ];
        })->values(), 200);
    }


    // GET /guardian/children
    public function children(Request $request)
    {
        $children = $request->user()->guardian->children()
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'sex', 'date_of_birth', 'photo_path'])
            ->map(fn (Child $child) => [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'middle_name' => $child->middle_name,
                'last_name' => $child->last_name,
                'sex' => $child->sex,
                'date_of_birth' => $child->date_of_birth,
                'photo_url' => $child->photo_path
                    ? 'api/v1/guardian/children/'.$child->id.'/photo'
                    : null,
            ]);

        return response()->json($children, 200);
    }

    // GET /guardian/children/{id}/dashboard
    public function dashboard(Request $request, int $id, EccdExpertSystem $expert)
    {
        $guardian = $request->user()->guardian;
        abort_unless($guardian, 404);

        $child = $guardian->children()->findOrFail($id);
        $progressRecord = $this->latestProgressRecord($child);

        $rounds = [];
        foreach (self::ROUNDS as $round) {
            $computed = $progressRecord
                ? $expert->compute($progressRecord, $round)
                : null;

            $rounds[$round] = [
                'date' => $this->roundDate($progressRecord, $round),
                'completed_domains' => $computed
                    ? count(array_filter($computed['raw_scores'], fn ($score) => $score !== null))
                    : 0,
                'standard_score' => $computed['standard_score'] ?? null,
                'interpretation' => $computed['interpretation']['overall'] ?? null,
                'domains' => [],
            ];

            foreach (self::DOMAIN_LABELS as $domainKey => $label) {
                $rounds[$round]['domains'][$domainKey] = [
                    'label' => $label,
                    'raw_score' => $computed['raw_scores'][$domainKey] ?? null,
                    'scaled_score' => $computed['scaled_scores'][$domainKey] ?? null,
                ];
            }
        }

        $domainRows = $progressRecord ? $this->domainRows($progressRecord) : collect();
        $lastUpdated = $this->lastUpdatedByDomain($domainRows);

        // Most recently updated domain first; domains with no entries keep the official order.
        $recentActivity = array_map(
            fn (string $domainKey) => [
                'domain' => $domainKey,
                'label' => self::DOMAIN_LABELS[$domainKey],
                'last_updated_at' => $lastUpdated[$domainKey]?->toIso8601String(),
            ],
            array_keys(self::DOMAIN_LABELS)
        );
        usort($recentActivity, fn (array $a, array $b) => ($lastUpdated[$b['domain']]?->getTimestamp() ?? 0)
            <=> ($lastUpdated[$a['domain']]?->getTimestamp() ?? 0));

        return response()->json([
            'child' => [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'middle_name' => $child->middle_name,
                'last_name' => $child->last_name,
                'sex' => $child->sex,
                'date_of_birth' => $child->date_of_birth,
                'photo_url' => $child->photo_path
                    ? 'api/v1/guardian/children/'.$child->id.'/photo'
                    : null,
            ],
            'progress' => [
                'status' => $progressRecord?->status ?? 'not_started',
                'evaluation_number' => $progressRecord?->evaluation_number,
                'latest_round' => $this->latestRound($progressRecord),
                'rounds' => $rounds,
                'recent_activity' => $recentActivity,
            ],
        ]);
    }

    // GET /guardian/children/{id}/domains/{domain}
    public function domain(Request $request, int $id, string $domain, EccdExpertSystem $expert)
    {
        abort_unless(array_key_exists($domain, self::DOMAIN_LABELS), 404);

        $guardian = $request->user()->guardian;
        abort_unless($guardian, 404);

        $child = $guardian->children()->findOrFail($id);
        $progressRecord = $this->latestProgressRecord($child);

        $domainRows = $progressRecord
            ? $this->domainRows($progressRecord)->filter(fn ($row) => $row->checklistItem?->domain === $domain)
            : collect();

        // [checklist item id][round] => recorded result
        $results = [];
        foreach ($domainRows as $row) {
            $result = $row->results->first();
            $results[$row->checklist_item_id][$row->evaluation_round] = $result ? [
                'present' => $expert->normalizePresentValue($result->present),
                'comments' => $result->comments,
            ] : null;
        }

        $items = EccdChecklistItem::query()
            ->where('domain', $domain)
            ->orderBy('item_number')
            ->orderBy('id')
            ->get(['id', 'item_number', 'activity'])
            ->map(fn (EccdChecklistItem $item) => [
                'id' => $item->id,
                'item_number' => $item->item_number,
                'activity' => $item->activity,
                'results' => array_combine(
                    self::ROUNDS,
                    array_map(fn (int $round) => $results[$item->id][$round] ?? null, self::ROUNDS)
                ),
            ])
            ->values();

        $rounds = [];
        foreach (self::ROUNDS as $round) {
            $computed = $progressRecord ? $expert->compute($progressRecord, $round) : null;

            $rounds[$round] = [
                'date' => $this->roundDate($progressRecord, $round),
                'raw_score' => $computed['raw_scores'][$domain] ?? null,
                'scaled_score' => $computed['scaled_scores'][$domain] ?? null,
                'assessed_items' => $items->filter(fn (array $item) => $item['results'][$round] !== null)->count(),
            ];
        }

        return response()->json([
            'child' => [
                'id' => $child->id,
                'first_name' => $child->first_name,
                'middle_name' => $child->middle_name,
                'last_name' => $child->last_name,
            ],
            'domain' => [
                'key' => $domain,
                'label' => self::DOMAIN_LABELS[$domain],
            ],
            'progress' => [
                'status' => $progressRecord?->status ?? 'not_started',
                'latest_round' => $this->latestRound($progressRecord),
                'last_updated_at' => $this->lastUpdatedByDomain($domainRows)[$domain]?->toIso8601String(),
                'rounds' => $rounds,
            ],
            'items' => $items,
        ]);
    }

    private function latestProgressRecord(Child $child): ?ProgressRecord
    {
        return ProgressRecord::query()
            ->where('child_id', $child->id)
            ->latest('updated_at')
            ->first();
    }

    private function roundDate(?ProgressRecord $progressRecord, int $round): mixed
    {
        return $progressRecord?->getAttribute("evaluation_date_round_{$round}")
            ?? ($round === 1 ? $progressRecord?->evaluation_date : null);
    }

    private function latestRound(?ProgressRecord $progressRecord): ?int
    {
        $latestRound = null;
        foreach (self::ROUNDS as $round) {
            if ($this->roundDate($progressRecord, $round) !== null) {
                $latestRound = $round;
            }
        }

        return $latestRound;
    }

    /** Checklist entries (all rounds) of a progress record, with their item and result. */
    private function domainRows(ProgressRecord $progressRecord): Collection
    {
        return $progressRecord->domains()
            ->with(['checklistItem:id,domain', 'results'])
            ->get();
    }

    /**
     * Latest time the teacher created or changed an entry in each domain.
     *
     * @return array<string, Carbon|null>
     */
    private function lastUpdatedByDomain(Collection $domainRows): array
    {
        $lastUpdated = array_fill_keys(array_keys(self::DOMAIN_LABELS), null);

        foreach ($domainRows as $row) {
            $domainKey = $row->checklistItem?->domain;
            if (! array_key_exists($domainKey ?? '', $lastUpdated)) {
                continue;
            }

            foreach ([$row->updated_at, ...$row->results->pluck('updated_at')] as $time) {
                if ($time !== null && ($lastUpdated[$domainKey] === null || $time->gt($lastUpdated[$domainKey]))) {
                    $lastUpdated[$domainKey] = $time;
                }
            }
        }

        return $lastUpdated;
    }

    // GET /guardian/children/{id}
    public function showChild(Request $request, $id)
    {
        $child = Child::where('guardian_id', $request->user()->guardian->id)
            ->findOrFail($id);

        // Explicit fields only: never expose storage paths or internal columns.
        return response()->json([
            'id' => $child->id,
            'first_name' => $child->first_name,
            'middle_name' => $child->middle_name,
            'last_name' => $child->last_name,
            'sex' => $child->sex,
            'date_of_birth' => $child->date_of_birth,
            'address' => $child->address,
            'handedness' => $child->handedness,
            'is_studying' => (bool) $child->is_studying,
            'school_name' => $child->school_name,
            'fathers_name' => $child->fathers_name,
            'fathers_age' => $child->fathers_age,
            'fathers_occupation' => $child->fathers_occupation,
            'fathers_education' => $child->fathers_education,
            'mothers_name' => $child->mothers_name,
            'mothers_age' => $child->mothers_age,
            'mothers_occupation' => $child->mothers_occupation,
            'mothers_education' => $child->mothers_education,
            'number_of_siblings' => $child->number_of_siblings,
            'birth_order' => $child->birth_order,
            'photo_url' => $child->photo_path
                ? 'api/v1/guardian/children/'.$child->id.'/photo'
                : null,
        ], 200);
    }

    public function photo(Request $request, int $id)
    {
        $guardian = $request->user()->guardian;
        abort_unless($guardian, 404);

        $child = $guardian->children()->findOrFail($id);
        abort_unless($child->photo_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($child->photo_path), 404);
        $mimeType = $disk->mimeType($child->photo_path);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'], true), 404);

        return $disk->response($child->photo_path, null, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    // GET /guardian/views
    public function views(Request $request)
    {
        $guardian = $request->user()->guardian;

        if (! $guardian) {
            return response()->json(['error' => 'Guardian profile not found'], 404);
        }

        // Load guardian with related children and their progress records/domains/scores
        $guardian->load([
            'children.progressRecords.teacher',
            'children.progressRecords.domains.results',
            'children.progressRecords.domainScores',
        ]);

        return response()->json([
            'profile' => $guardian,
            'children' => $guardian->children,
        ], 200);
    }
}