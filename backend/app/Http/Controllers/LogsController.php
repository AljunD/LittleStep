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
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
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

        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('created_at', [
                $filters['from_date'].' 00:00:00',
                $filters['to_date'].' 23:59:59',
            ]);
        }

        if (! empty($filters['search'])) {
            $query->where('details', 'like', '%'.$filters['search'].'%');
        }

        $logs = $query->paginate(15)->withQueryString();
        $users = collect([$request->user()]);

        return view('logs.index', compact('logs', 'users'));
    }
}
