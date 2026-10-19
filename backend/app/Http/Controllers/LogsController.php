<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogIndexRequest;
use App\Models\Log;
use App\Support\Search;

class LogsController extends Controller
{
    public function index(LogIndexRequest $request)
    {
        $filters = $request->validated();
        $ownLogs = fn () => Log::where('user_id', $request->user()->id);

        $query = $ownLogs()->latest();

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        Search::apply($query, $request->search(), ['details']);

        $logs = $query->paginate(15)->withQueryString();
        $actions = $ownLogs()->distinct()->orderBy('action')->pluck('action');
        $entityTypes = $ownLogs()->distinct()->orderBy('entity_type')->pluck('entity_type');

        return view('logs.index', compact('logs', 'actions', 'entityTypes'));
    }
}
