<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuardianController extends Controller
{
    // GET /guardian/profile
    public function profile(Request $request)
    {
        return response()->json($request->user()->guardian, 200);
    }

    // GET /guardian/children
    public function children(Request $request)
    {
        return response()->json($request->user()->guardian->children, 200);
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
