<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with dynamic database stats.
     */
    public function index()
    {   
        // 1. Classroom Performance Metrics (KPI Cards)
        $guardiansCount = DB::table('guardians')->whereNull('deleted_at')->count();
        
        // Explicitly query 'children' table based on your migration override
        $childrenCount = DB::table('children')->whereNull('deleted_at')->count();
        
        $completedRecordsCount = DB::table('progress_records')
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->count();
            
        $unfinishedRecordsCount = DB::table('progress_records')
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNull('deleted_at')
            ->count();

        // 2. Evaluation Action Alerts (Urgent red panel)
        // Fetches the oldest pending record that needs layout initialization
        $rawUrgentRecord = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->where('progress_records.status', 'pending')
            ->whereNull('progress_records.deleted_at')
            ->select(
                'progress_records.*',
                'children.first_name as child_first_name',
                'children.middle_name as child_middle_name',
                'children.last_name as child_last_name'
            )
            ->orderBy('progress_records.created_at', 'asc')
            ->first();

        // Transform raw data into an object mimicking Eloquent relationship properties for the Blade template
        $urgentAlertRecord = null;
        if ($rawUrgentRecord) {
            $urgentAlertRecord = (object) [
                'status' => $rawUrgentRecord->status,
                'evaluation_number' => $rawUrgentRecord->evaluation_number,
                'child' => (object) [
                    'first_name' => $rawUrgentRecord->child_first_name,
                    'middle_name' => $rawUrgentRecord->child_middle_name,
                    'last_name' => $rawUrgentRecord->child_last_name,
                ]
            ];
        }

        // 3. Incomplete Tasks Queue (Yellow lists)
        $rawIncompleteTasks = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->where('progress_records.status', 'in_progress')
            ->whereNull('progress_records.deleted_at')
            ->select(
                'progress_records.*',
                'children.first_name as child_first_name',
                'children.middle_name as child_middle_name',
                'children.last_name as child_last_name'
            )
            ->orderBy('progress_records.updated_at', 'desc')
            ->take(2)
            ->get();

        $incompleteTasksQueue = $rawIncompleteTasks->map(function ($item) {
            return (object) [
                'status' => $item->status,
                'child' => (object) [
                    'first_name' => $item->child_first_name,
                    'middle_name' => $item->child_middle_name,
                    'last_name' => $item->child_last_name,
                ]
            ];
        });

        // 4. Teacher Activity History Log
        $rawLogs = DB::table('logs')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentLogs = $rawLogs->map(function ($log) {
            return (object) [
                'action' => $log->action,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'details' => $log->details,
                'created_at' => Carbon::parse($log->created_at),
            ];
        });

        // 5. Master Student Evaluation Registry (Data Table)
        $rawRegistry = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->join('teachers', 'progress_records.teacher_id', '=', 'teachers.id')
            ->whereNull('progress_records.deleted_at')
            ->select(
                'progress_records.id as record_id',
                'progress_records.evaluation_date',
                'progress_records.evaluation_number',
                'progress_records.status',
                'children.first_name as child_first_name',
                'children.middle_name as child_middle_name',
                'children.last_name as child_last_name',
                'teachers.first_name as teacher_first_name',
                'teachers.last_name as teacher_last_name'
            )
            ->orderBy('progress_records.evaluation_date', 'desc')
            ->orderBy('progress_records.id', 'desc')
            ->take(10)
            ->get();

        $registryRecords = $rawRegistry->map(function ($row) {
            return (object) [
                'evaluation_date' => $row->evaluation_date,
                'evaluation_number' => $row->evaluation_number,
                'status' => $row->status,
                'child' => (object) [
                    'first_name' => $row->child_first_name,
                    'middle_name' => $row->child_middle_name,
                    'last_name' => $row->child_last_name,
                ],
                'teacher' => (object) [
                    'first_name' => $row->teacher_first_name,
                    'last_name' => $row->teacher_last_name,
                ]
            ];
        });

        // 6. Return payload directly to layout template variable binders
        return view('dashboard', compact(
            'guardiansCount',
            'childrenCount',
            'completedRecordsCount',
            'unfinishedRecordsCount',
            'urgentAlertRecord',
            'incompleteTasksQueue',
            'recentLogs',
            'registryRecords'
        ));
    }
}