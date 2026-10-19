<?php

namespace App\Http\Controllers;

use App\Services\DashboardInsightsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardInsightsService $insightsService)
    {
        $teacher = $request->user()?->teacher;
        if (! $teacher) {
            throw new HttpException(403, 'A teacher profile is required to view this dashboard.');
        }

        $teacherId = $teacher->id;

        $guardiansCount = DB::table('guardians')
            ->join('children', 'guardians.id', '=', 'children.guardian_id')
            ->join('progress_records', 'children.id', '=', 'progress_records.child_id')
            ->where('progress_records.teacher_id', $teacherId)
            ->whereNull('guardians.deleted_at')
            ->whereNull('children.deleted_at')
            ->whereNull('progress_records.deleted_at')
            ->distinct()
            ->count('guardians.id');

        $childrenCount = DB::table('children')
            ->join('progress_records', 'children.id', '=', 'progress_records.child_id')
            ->where('progress_records.teacher_id', $teacherId)
            ->whereNull('children.deleted_at')
            ->whereNull('progress_records.deleted_at')
            ->distinct()
            ->count('children.id');

        $completedRecordsCount = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->where('progress_records.teacher_id', $teacherId)
            ->where('progress_records.status', 'completed')
            ->whereNull('progress_records.deleted_at')
            ->whereNull('children.deleted_at')
            ->count();

        $unfinishedRecordsCount = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->where('progress_records.teacher_id', $teacherId)
            ->whereIn('progress_records.status', ['pending', 'in_progress'])
            ->whereNull('progress_records.deleted_at')
            ->whereNull('children.deleted_at')
            ->count();

        $rawUrgentRecord = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->where('progress_records.teacher_id', $teacherId)
            ->where('progress_records.status', 'pending')
            ->whereNull('progress_records.deleted_at')
            ->whereNull('children.deleted_at')
            ->select(
                'progress_records.*',
                'children.first_name as child_first_name',
                'children.middle_name as child_middle_name',
                'children.last_name as child_last_name'
            )
            ->orderBy('progress_records.created_at', 'asc')
            ->first();

        $urgentAlertRecord = null;
        if ($rawUrgentRecord) {
            $urgentAlertRecord = (object) [
                'status' => $rawUrgentRecord->status,
                'evaluation_number' => $rawUrgentRecord->evaluation_number,
                'child' => (object) [
                    'first_name' => $rawUrgentRecord->child_first_name,
                    'middle_name' => $rawUrgentRecord->child_middle_name,
                    'last_name' => $rawUrgentRecord->child_last_name,
                ],
            ];
        }

        $rawIncompleteTasks = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->where('progress_records.teacher_id', $teacherId)
            ->where('progress_records.status', 'in_progress')
            ->whereNull('progress_records.deleted_at')
            ->whereNull('children.deleted_at')
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
                ],
            ];
        });

        $rawRegistry = DB::table('progress_records')
            ->join('children', 'progress_records.child_id', '=', 'children.id')
            ->join('teachers', 'progress_records.teacher_id', '=', 'teachers.id')
            ->where('progress_records.teacher_id', $teacherId)
            ->whereNull('progress_records.deleted_at')
            ->whereNull('children.deleted_at')
            ->whereNull('teachers.deleted_at')
            ->select(
                'progress_records.id as record_id',
                DB::raw('COALESCE(progress_records.evaluation_date_round_3, progress_records.evaluation_date_round_2, progress_records.evaluation_date_round_1, progress_records.evaluation_date) as evaluation_date'),
                'progress_records.evaluation_number',
                'progress_records.status',
                'children.first_name as child_first_name',
                'children.middle_name as child_middle_name',
                'children.last_name as child_last_name',
                'teachers.first_name as teacher_first_name',
                'teachers.last_name as teacher_last_name'
            )
            ->orderByRaw('COALESCE(progress_records.evaluation_date_round_3, progress_records.evaluation_date_round_2, progress_records.evaluation_date_round_1, progress_records.evaluation_date) desc')
            ->orderBy('progress_records.id', 'desc')
            ->take(10)
            ->get();

        $registryRecords = $rawRegistry->map(function ($row) {
            return (object) [
                'id' => $row->record_id,
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
                ],
            ];
        });

        $insights = $insightsService->for($teacher);

        return view('dashboard', compact(
            'guardiansCount',
            'childrenCount',
            'completedRecordsCount',
            'unfinishedRecordsCount',
            'urgentAlertRecord',
            'incompleteTasksQueue',
            'registryRecords',
            'insights'
        ));
    }
}
