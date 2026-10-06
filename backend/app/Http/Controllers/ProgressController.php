<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\ProgressRecord;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * Display a listing of all children with their progress records.
     */
    public function index()
    {
        // Kukunin ang lahat ng bata at ang kanilang pinakabagong progress record
        $children = Child::with(['progressRecords' => function ($query) {
            $query->latest();
        }, 'progressRecords.teacher'])
        ->latest()
        ->paginate(10);

        return view('progress.index', compact('children'));
    }

    /**
     * Show the domain selection view for evaluation.
     */
    public function selectDomain($child_id = null)
    {
        $child = $child_id ? Child::find($child_id) : null;
        return view('progress.select-domain', compact('child', 'child_id'));
    }

    /**
     * Show form for creating a new evaluation.
     */
    public function create($child_id = null)
    {
        $child = $child_id ? Child::find($child_id) : null;
        return view('progress.create', compact('child', 'child_id'));
    }

    /**
     * Show form for adding an observation.
     */
    public function addObservation($child_id = null)
    {
        $child = $child_id ? Child::find($child_id) : null;
        return view('progress.create-observation', compact('child', 'child_id'));
    }

    /**
     * Display the specified progress record details or child progress summary.
     */
    public function show($id = null)
    {
        $progressRecord = ProgressRecord::with(['child', 'teacher', 'domains', 'domainScores', 'domainObservation'])->find($id);

        if (!$progressRecord) {
            $progressRecord = ProgressRecord::with(['child', 'teacher', 'domains', 'domainScores', 'domainObservation'])
                ->where('child_id', $id)
                ->latest()
                ->first();
        }

        $child = $progressRecord ? $progressRecord->child : Child::find($id);

        return view('progress.show', compact('progressRecord', 'child', 'id'));
    }

    /**
     * Show form for editing a progress record.
     */
    public function edit($id = null)
    {
        $progressRecord = $id ? ProgressRecord::with(['child', 'teacher'])->find($id) : null;
        return view('progress.edit', compact('progressRecord', 'id'));
    }
}