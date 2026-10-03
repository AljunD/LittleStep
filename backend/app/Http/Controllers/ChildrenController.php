<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Child;
use App\Models\Guardian;
use Illuminate\Support\Facades\Storage;

class ChildrenController extends Controller
{
    /**
     * Display a listing of children.
     */
    public function index()
    {
        $children = Child::with('guardian')->paginate(10);
        return view('children.index', compact('children'));
    }

    /**
     * Show the form for creating a new child.
     */
    public function create()
    {
        $guardians = Guardian::all();
        return view('children.create', compact('guardians'));
    }

    /**
     * Store a newly created child in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'guardian_id'   => 'required|exists:guardians,id',
            'first_name'    => 'required|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'last_name'     => 'required|string|max:255',
            'sex'           => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'address'       => 'nullable|string|max:500',
        ]);

        $child = Child::create($data);

        // Log creation
        recordLog('created', 'Child', $child->id, 'Child created: ' . $child->first_name . ' ' . $child->last_name);

        return redirect()->route('children.index')
                         ->with('success', 'Child created successfully.');
    }

    /**
     * Display the specified child.
     */
    public function show($id)
    {
        $child = Child::with('guardian.user')->findOrFail($id);
        return view('children.show', compact('child'));
    }

    /**
     * Show the form for editing the specified child.
     */
    public function edit($id)
    {
        $child = Child::findOrFail($id);
        $guardians = Guardian::all();
        return view('children.edit', compact('child', 'guardians'));
    }

    /**
     * Update the specified child in storage.
     */
    public function update(Request $request, $id)
    {
        $child = Child::findOrFail($id);

        $data = $request->validate([
            'first_name'         => 'required|string|max:255',
            'middle_name'        => 'nullable|string|max:255',
            'last_name'          => 'required|string|max:255',
            'sex'                => 'required|in:Male,Female',
            'date_of_birth'      => 'required|date',
            'barangay'           => 'nullable|string|max:255',
            'municipality'       => 'nullable|string|max:255',
            'province'           => 'nullable|string|max:255',
            'region'             => 'nullable|string|max:255',
            'handedness'         => 'nullable|in:right,left,both,not_yet_established',
            'is_studying'        => 'nullable|boolean',
            'school_name'        => 'nullable|string|max:255',
            'fathers_name'       => 'nullable|string|max:255',
            'fathers_age'        => 'nullable|integer|min:0|max:120',
            'fathers_occupation' => 'nullable|string|max:255',
            'fathers_education'  => 'nullable|string|max:255',
            'mothers_name'       => 'nullable|string|max:255',
            'mothers_age'        => 'nullable|integer|min:0|max:120',
            'mothers_occupation' => 'nullable|string|max:255',
            'mothers_education'  => 'nullable|string|max:255',
            'number_of_siblings' => 'nullable|integer|min:0',
            'birth_order'        => 'nullable|integer|min:1',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $addressParts = array_filter([
            $request->input('barangay'),
            $request->input('municipality'),
            $request->input('province'),
            $request->input('region'),
        ]);
        $data['address'] = !empty($addressParts) ? implode(', ', $addressParts) : null;

        if ($request->hasFile('photo')) {
            if ($child->photo_path && Storage::disk('public')->exists($child->photo_path)) {
                Storage::disk('public')->delete($child->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('photos/children', 'public');
        }

        $data['is_studying'] = $request->input('is_studying') == '1' ? 1 : 0;

        // If child is not studying, set school_name to null
        if ($data['is_studying'] === 0) {
            $data['school_name'] = null;
        }

        unset($data['photo']);

        $child->update($data);

        recordLog('updated', 'Child', $child->id, 'Child updated: ' . $child->first_name . ' ' . $child->last_name);

        return redirect()->route('children.show', $child->id)
                         ->with('success', 'Child profile updated successfully.');
    }

    /**
     * Soft delete the specified child.
     */
    public function destroy($id)
    {
        $child = Child::findOrFail($id);
        $child->delete();

        // Log deletion
        recordLog('deleted', 'Child', $child->id, 'Child archived: ' . $child->first_name . ' ' . $child->last_name);

        return redirect()->route('children.index')
                         ->with('success', 'Child archived successfully.');
    }
}