<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\User;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('search')) {
            $query->where('details', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(15)->withQueryString();

        $users = User::orderBy('email')->get();

        return view('logs.index', compact('logs', 'users'));
    }
}
