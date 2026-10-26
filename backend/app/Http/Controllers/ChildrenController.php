<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChildIndexRequest;
use App\Models\Teacher;
use App\Services\ChildProfileSummary;
use App\Support\FormRules;
use App\Support\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChildrenController extends Controller
{
    public function index(ChildIndexRequest $request)
    {
        $teacher = $this->currentTeacher($request);
        $children = Search::apply($teacher->ownedChildren(), $request->search(), [
            'first_name', 'middle_name', 'last_name', 'guardian.first_name', 'guardian.last_name',
        ])
            ->when($request->validated('sex'), fn ($query, $sex) => $query->where('sex', $sex))
            ->with('guardian')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return view('children.index', compact('children'));
    }

    public function show(Request $request, $id, ChildProfileSummary $profileSummary)
    {
        $teacher = $this->currentTeacher($request);
        // Archived children stay viewable from the Archives page.
        $child = $teacher->ownedChildren()
            ->withTrashed()
            ->with(['guardian' => fn ($query) => $query->withTrashed()->with(['user' => fn ($query) => $query->withTrashed()])])
            ->findOrFail($id);
        recordLog('viewed', 'Child', $child->id, 'Child record viewed.');

        $summary = $profileSummary->for($teacher, $child);

        return view('children.show', compact('child', 'summary'));
    }

    public function edit(Request $request, $id)
    {
        $teacher = $this->currentTeacher($request);
        $child = $teacher->ownedChildren()->findOrFail($id);
        $guardians = $teacher->ownedGuardians()->get();

        return view('children.edit', compact('child', 'guardians'));
    }

    public function update(Request $request, $id)
    {
        $child = $this->currentTeacher($request)->ownedChildren()->findOrFail($id);

        $data = $request->validate(
            FormRules::childProfile('', [$child->fathers_education, $child->mothers_education]),
            FormRules::messages()
        );

        $data['address'] = FormRules::joinAddress($data) ?: null;
        unset($data['barangay'], $data['municipality'], $data['province'], $data['region'], $data['photo']);

        if ($request->hasFile('photo')) {
            $oldPhotoPath = $child->photo_path;
            $data['photo_path'] = $request->file('photo')->store('photos/children', 'local');
        }

        $data['is_studying'] = (bool) ($data['is_studying'] ?? false);
        if (! $data['is_studying']) {
            $data['school_name'] = null;
        }

        $child->update($data);

        if (isset($oldPhotoPath)) {
            Storage::disk('local')->delete($oldPhotoPath);
        }

        recordLog('updated', 'Child', $child->id, 'Child updated: '.$child->first_name.' '.$child->last_name);

        return redirect()->route('children.show', $child->id)
            ->with('success', 'Child profile updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $child = $this->currentTeacher($request)->ownedChildren()->findOrFail($id);
        $child->delete();

        recordLog('deleted', 'Child', $child->id, 'Child archived: '.$child->first_name.' '.$child->last_name);

        return redirect()->route('children.index')
            ->with('success', 'Child archived successfully.');
    }

    public function photo(Request $request, int $id)
    {
        $child = $this->currentTeacher($request)->ownedChildren()->withTrashed()->findOrFail($id);
        abort_unless($child->photo_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($child->photo_path), 404);
        $mimeType = $disk->mimeType($child->photo_path);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'], true), 404);

        recordLog('viewed', 'Child', $child->id, 'Child photo viewed.');

        return $disk->response($child->photo_path, null, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    private function currentTeacher(Request $request): Teacher
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');

        return $teacher;
    }
}
