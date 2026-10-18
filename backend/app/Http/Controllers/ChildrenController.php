<?php

namespace App\Http\Controllers;

use App\Models\ProgressRecord;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChildrenController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $this->currentTeacher($request);
        $children = $teacher->ownedChildren()->with('guardian')->paginate(10);

        return view('children.index', compact('children'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guardian_id' => 'required|integer|exists:guardians,id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'address' => 'nullable|string|max:500',
        ]);

        $teacher = $this->currentTeacher($request);
        $guardian = $teacher->ownedGuardians()->findOrFail($data['guardian_id']);
        $data['handedness'] = 'not_yet_established';

        $child = DB::transaction(function () use ($guardian, $data, $teacher) {
            $child = $guardian->children()->create($data);
            ProgressRecord::create([
                'child_id' => $child->id,
                'teacher_id' => $teacher->id,
                'evaluation_date' => now()->toDateString(),
                'evaluation_date_round_1' => now()->toDateString(),
                'evaluation_number' => 1,
                'status' => 'pending',
            ]);

            return $child;
        });

        // Log creation
        recordLog('created', 'Child', $child->id, 'Child created: '.$child->first_name.' '.$child->last_name);

        return redirect()->route('children.index')
            ->with('success', 'Child created successfully.');
    }

    public function show(Request $request, $id)
    {
        $child = $this->currentTeacher($request)->ownedChildren()
            ->with('guardian.user')
            ->findOrFail($id);
        recordLog('viewed', 'Child', $child->id, 'Child record viewed.');

        return view('children.show', compact('child'));
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

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'required|in:Male,Female',
            'date_of_birth' => 'required|date',
            'barangay' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'handedness' => 'nullable|in:right,left,both,not_yet_established',
            'is_studying' => 'nullable|boolean',
            'school_name' => 'nullable|string|max:255',
            'fathers_name' => 'nullable|string|max:255',
            'fathers_age' => 'nullable|integer|min:0|max:120',
            'fathers_occupation' => 'nullable|string|max:255',
            'fathers_education' => 'nullable|string|max:255',
            'mothers_name' => 'nullable|string|max:255',
            'mothers_age' => 'nullable|integer|min:0|max:120',
            'mothers_occupation' => 'nullable|string|max:255',
            'mothers_education' => 'nullable|string|max:255',
            'number_of_siblings' => 'nullable|integer|min:0',
            'birth_order' => 'nullable|integer|min:1',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:2048',
        ]);

        $addressParts = array_filter([
            $request->input('barangay'),
            $request->input('municipality'),
            $request->input('province'),
            $request->input('region'),
        ]);
        $data['address'] = ! empty($addressParts) ? implode(', ', $addressParts) : null;

        if ($request->hasFile('photo')) {
            $oldPhotoPath = $child->photo_path;
            $data['photo_path'] = $request->file('photo')->store('photos/children', 'local');
        }

        $data['is_studying'] = $request->input('is_studying') == '1' ? 1 : 0;

        if ($data['is_studying'] === 0) {
            $data['school_name'] = null;
        }

        unset($data['photo']);

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
        $child = $this->currentTeacher($request)->ownedChildren()->findOrFail($id);
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
