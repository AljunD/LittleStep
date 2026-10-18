<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ProgressRecord;
use App\Services\EccdExpertSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuardianController extends Controller
{
    private const DASHBOARD_DOMAINS = [
        'gross_motor' => 'Gross Motor',
        'fine_motor' => 'Fine Motor',
        'cognitive' => 'Cognitive',
    ];

    // GET /guardian/profile
    public function profile(Request $request)
    {
        $guardian = $request->user()->guardian;

        return response()->json([
            'id' => $guardian->id,
            'first_name' => $guardian->first_name,
            'middle_name' => $guardian->middle_name,
            'last_name' => $guardian->last_name,
        ], 200);
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
        $progressRecord = ProgressRecord::query()
            ->where('child_id', $child->id)
            ->latest('updated_at')
            ->first();

        $rounds = [];
        foreach ([1, 2, 3] as $round) {
            $date = $progressRecord?->getAttribute("evaluation_date_round_{$round}")
                ?? ($round === 1 ? $progressRecord?->evaluation_date : null);
            $computed = $progressRecord
                ? $expert->compute($progressRecord, $round)
                : null;

            $rounds[$round] = [
                'date' => $date,
                'completed_domains' => $computed
                    ? count(array_filter($computed['raw_scores'], fn ($score) => $score !== null))
                    : 0,
                'standard_score' => $computed['standard_score'] ?? null,
                'interpretation' => $computed['interpretation']['overall'] ?? null,
                'domains' => [],
            ];

            foreach (self::DASHBOARD_DOMAINS as $domainKey => $label) {
                $rounds[$round]['domains'][$domainKey] = [
                    'label' => $label,
                    'raw_score' => $computed['raw_scores'][$domainKey] ?? null,
                    'scaled_score' => $computed['scaled_scores'][$domainKey] ?? null,
                ];
            }
        }

        $latestRound = null;
        foreach ($rounds as $round => $roundData) {
            if ($roundData['date'] !== null) {
                $latestRound = $round;
            }
        }

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
                'latest_round' => $latestRound,
                'rounds' => $rounds,
            ],
        ]);
    }

    // GET /guardian/children/{id}
    public function showChild(Request $request, $id)
    {
        $child = Child::where('guardian_id', $request->user()->guardian->id)
            ->findOrFail($id);

        return response()->json($child, 200);
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