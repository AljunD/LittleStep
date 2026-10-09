<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Log;

if (!function_exists('recordLog')) {
    /**
     * Record a system log entry.
     *
     * @param string      $action
     * @param string      $entityType
     * @param int         $entityId
     * @param string|null $details
     * @param int|null    $userId   Optional override for user_id
     */
    function recordLog(string $action, string $entityType, int $entityId, ?string $details = null, ?int $userId = null): void
    {
        Log::create([
            'user_id'     => $userId ?? Auth::id(), 
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details,
        ]);
    }
}
