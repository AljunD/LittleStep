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
        $childId = $child_id ?? request('child_id');
        $child = $childId ? Child::find($childId) : null;

        // Fetch latest progress record for child
        $latestRecord = $child ? ProgressRecord::with(['domains', 'domainScores'])
            ->where('child_id', $child->id)
            ->latest()
            ->first() : null;

        // Standard 7 ECCD Domains
        $definedDomains = [
            'gross_motor'        => 'Gross Motor',
            'fine_motor'         => 'Fine Motor',
            'self_help'          => 'Self Help',
            'receptive_language' => 'Receptive Language',
            'expressive_language'=> 'Expressive Language',
            'cognitive'          => 'Cognitive',
            'social_emotional'   => 'Social Emotional',
        ];

        // Map status for each domain dynamically
        $domains = [];
        foreach ($definedDomains as $key => $name) {
            $status = 'Pending';

            if ($latestRecord) {
                // Check if score exists for domain
                $score = $latestRecord->domainScores->where('domain_name', $key)->first()
                    ?? $latestRecord->domains->where('name', $key)->first();

                if ($score) {
                    $status = $score->status ?? ($score->is_completed ? 'Completed' : 'In Progress');
                }
            }

            $domains[] = [
                'key'    => $key,
                'name'   => $name,
                'status' => $status,
            ];
        }

        return view('progress.select-domain', compact('child', 'childId', 'domains'));
    }

    /**
     * Show form for creating a new evaluation.
     */
    public function create($child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        $child = $childId ? Child::find($childId) : null;
        return view('progress.create', compact('child', 'childId'));
    }

    /**
     * Show form for adding an observation.
     */
    public function addObservation($child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        $child = $childId ? Child::find($childId) : null;
        return view('progress.create-observation', compact('child', 'childId'));
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