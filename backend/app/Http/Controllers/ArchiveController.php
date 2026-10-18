<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $this->currentTeacher($request);

        $archivedGuardians = Guardian::onlyTrashed()
            ->whereHas('children', function ($query) use ($teacher) {
                $query->withTrashed()
                    ->whereHas('progressRecords', function ($progressQuery) use ($teacher) {
                        $progressQuery->withTrashed()->where('teacher_id', $teacher->id);
                    });
            })
            ->with(['user' => function ($query) {
                $query->withTrashed();
            }])
            ->paginate(10, ['*'], 'guardians_page')
            ->appends(['tab' => 'guardians']);

        $archivedChildren = Child::onlyTrashed()
            ->whereHas('progressRecords', function ($query) use ($teacher) {
                $query->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->with(['guardian' => function ($query) {
                $query->withTrashed();
            }])
            ->paginate(10, ['*'], 'children_page')
            ->appends(['tab' => 'children']);

        $activeTab = $request->query('tab') === 'children' ? 'children' : 'guardians';

        return view('archives.index', compact('archivedGuardians', 'archivedChildren', 'activeTab'));
    }

    public function restore(Request $request, int $id)
    {
        $teacher = $this->currentTeacher($request);
        $guardian = Guardian::onlyTrashed()
            ->whereHas('children', function ($query) use ($teacher) {
                $query->withTrashed()
                    ->whereHas('progressRecords', function ($progressQuery) use ($teacher) {
                        $progressQuery->withTrashed()->where('teacher_id', $teacher->id);
                    });
            })
            ->whereDoesntHave('children', function ($query) use ($teacher) {
                $query->withTrashed()
                    ->whereDoesntHave('progressRecords', function ($progressQuery) use ($teacher) {
                        $progressQuery->withTrashed()->where('teacher_id', $teacher->id);
                    });
            })
            ->with(['user' => function ($query) {
                $query->withTrashed();
            }])
            ->findOrFail($id);

        $guardian->restore();
        $guardian->user?->restore();

        if (function_exists('recordLog')) {
            recordLog('restored', 'Guardian', $guardian->id, 'Guardian and linked user restored: '.$guardian->first_name.' '.$guardian->last_name);
        }

        return redirect()->route('archives.index')
            ->with('success', 'Guardian and linked user restored successfully.');
    }

    public function restoreChild(Request $request, int $id)
    {
        $teacher = $this->currentTeacher($request);
        $child = Child::onlyTrashed()
            ->whereHas('progressRecords', function ($query) use ($teacher) {
                $query->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->findOrFail($id);

        $child->restore();

        if (function_exists('recordLog')) {
            recordLog('restored', 'Child', $child->id, 'Child restored: '.$child->first_name.' '.$child->last_name);
        }

        return redirect()->route('archives.index', ['tab' => 'children'])
            ->with('success', 'Child record restored successfully.');
    }

    private function currentTeacher(Request $request): Teacher
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');

        return $teacher;
    }
}
