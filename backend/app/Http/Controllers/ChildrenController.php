<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Child;
use App\Models\Guardian;

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
            'guardian_id'   => 'required|exists:guardians,id',
            'first_name'    => 'required|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'last_name'     => 'required|string|max:255',
            'sex'           => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'address'       => 'nullable|string|max:500',
        ]);

        $child->update($data);

        // Log update
        recordLog('updated', 'Child', $child->id, 'Child updated: ' . $child->first_name . ' ' . $child->last_name);

        return redirect()->route('children.index')
                         ->with('success', 'Child updated successfully.');
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