<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogIndexRequest;
use App\Services\ActivityLogService;
use App\Support\AuditLog;
use Illuminate\Http\Request;

/** System Logs: the signed-in teacher's own audit trail. */
class LogsController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function index(LogIndexRequest $request)
    {
        $user = $request->user();
        $logs = $this->activityLogs->filteredQuery($user, $request->validated())
            ->paginate(ActivityLogService::PER_PAGE)
            ->withQueryString();

        return view('logs.index', [
            'logs' => $logs,
            'recordLinks' => $this->activityLogs->recordLinks($user, $logs->getCollection()),
            'summary' => $this->activityLogs->summary($user),
            'actionOptions' => $this->activityLogs->ownDistinct($user, 'action')
                ->mapWithKeys(fn (string $action) => [$action => AuditLog::actionLabel($action)]),
            'entityOptions' => $this->activityLogs->ownDistinct($user, 'entity_type')
                ->mapWithKeys(fn (string $type) => [$type => AuditLog::entityLabel($type)]),
        ]);
    }

    public function show(Request $request, int $log)
    {
        $user = $request->user();
        // Scoped to the user's own entries: anyone else's id is a 404, not a 403, so ids can't be probed.
        $entry = $this->activityLogs->ownLogs($user)->findOrFail($log);

        return view('logs.show', [
            'log' => $entry,
            'recordUrl' => $this->activityLogs->recordLinks($user, [$entry])["{$entry->entity_type}:{$entry->entity_id}"] ?? null,
        ]);
    }

    public function export(LogIndexRequest $request)
    {
        return $this->activityLogs->exportCsv($request->user(), $request->validated());
    }
}
