<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Domain;
use App\Models\DomainResult;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function index()
    {
        $children = Child::with(['progressRecords' => function ($query) {
            $query->latest();
        }, 'progressRecords.teacher'])
        ->latest()
        ->paginate(10);

        return view('progress.index', compact('children'));
    }

    public function selectDomain($child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        $child = $childId ? Child::find($childId) : null;

        $latestRecord = $child ? ProgressRecord::with(['domains', 'domainScores'])
            ->where('child_id', $child->id)
            ->latest()
            ->first() : null;

        $definedDomains = [
            'gross_motor'        => 'Gross Motor',
            'fine_motor'         => 'Fine Motor',
            'self_help'          => 'Self Help',
            'receptive_language' => 'Receptive Language',
            'expressive_language'=> 'Expressive Language',
            'cognitive'          => 'Cognitive',
            'social_emotional'   => 'Social Emotional',
        ];

        $domains = [];
        foreach ($definedDomains as $key => $name) {
            $status = 'Pending';

            if ($latestRecord) {
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

    public function create(Request $request, $child_id = null)
    {
        $child = $child_id ? Child::findOrFail($child_id) : null;
        $domainKey = $request->query('domain'); 

        $allowed = [
            'gross_motor', 'fine_motor', 'self_help',
            'receptive_language', 'expressive_language',
            'cognitive', 'social_emotional'
        ];

        if (!in_array($domainKey, $allowed)) {
            abort(404, 'Invalid domain');
        }

        $items = \App\Models\Domain::whereNull('progress_record_id')
            ->where('domain', $domainKey)
            ->orderBy('id')
            ->get();

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
            'child'       => $child,
            'domainKey'   => $domainKey,
            'domainName'  => $domainNames[$domainKey],
            'items'       => $items,
        ]);
    }
    

    public function addObservation($child_id = null)
    {
        $childId = $child_id ?? request('child_id');
        $child = $childId ? Child::find($childId) : null;
        return view('progress.create-observation', compact('child', 'childId'));
    }

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

    public function edit($id = null)
    {
        $progressRecord = $id ? ProgressRecord::with(['child', 'teacher'])->find($id) : null;
        return view('progress.edit', compact('progressRecord', 'id'));
    }
}