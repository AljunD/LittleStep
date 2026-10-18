<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:50'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => array_filter(['nullable', 'date', $request->filled('from_date') ? 'after_or_equal:from_date' : null]),
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Log::with('user')
            ->where('user_id', $request->user()->id)
            ->latest();

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        // Either date works on its own (previously both were required for any date filtering).
        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (! empty($filters['search'])) {
            // Treat % and _ typed by the user as literal characters, not LIKE wildcards.
            // "!" is the escape character because MySQL and SQLite both accept it in ESCAPE.
            $search = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']);
            $query->whereRaw("details LIKE ? ESCAPE '!'", ['%'.$search.'%']);
        }

        $logs = $query->paginate(15)->withQueryString();
        $users = collect([$request->user()]);

        return view('logs.index', compact('logs', 'users'));
    }
}
