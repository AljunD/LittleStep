<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guardian;
use App\Models\Child;

class ArchiveController extends Controller
{
    /**
     * Display a listing of archived guardians and archived children.
     */
    public function index(Request $request)
    {
        // Fetch soft-deleted guardians with linked user accounts
        $archivedGuardians = Guardian::onlyTrashed()
            ->with(['user' => function ($query) {
                $query->withTrashed();
            }])
            ->paginate(10, ['*'], 'guardians_page');

        // Fetch soft-deleted children with linked guardians
        $archivedChildren = Child::onlyTrashed()
            ->with(['guardian' => function ($query) {
                $query->withTrashed();
            }])
            ->paginate(10, ['*'], 'children_page');

        return view('archives.index', compact('archivedGuardians', 'archivedChildren'));
    }

    /**
     * Restore an archived guardian and linked user.
     */
    public function restore($id)
    {
        try {
            $guardian = Guardian::withTrashed()->findOrFail($id);
            $guardian->restore();

            if ($guardian->user) {
                $guardian->user->restore();
            }

            if (function_exists('recordLog')) {
                recordLog('restored', 'Guardian', $guardian->id, 'Guardian and linked user restored: ' . $guardian->first_name . ' ' . $guardian->last_name);
            }

            return redirect()->route('archives.index')
                             ->with('success', 'Guardian and linked user restored successfully.');
        } catch (\Exception $e) {
            return redirect()->route('archives.index')
                             ->with('error', 'Failed to restore guardian. Please try again.');
        }
    }

    /**
     * Restore an archived child.
     */
    public function restoreChild($id)
    {
        try {
            $child = Child::withTrashed()->findOrFail($id);
            $child->restore();

            if (function_exists('recordLog')) {
                recordLog('restored', 'Child', $child->id, 'Child restored: ' . $child->first_name . ' ' . $child->last_name);
            }

            return redirect()->route('archives.index')
                             ->with('success', 'Child record restored successfully.');
        } catch (\Exception $e) {
            return redirect()->route('archives.index')
                             ->with('error', 'Failed to restore child record. Please try again.');
        }
    }
}