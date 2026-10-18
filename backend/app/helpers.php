<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Log;

if (!function_exists('recordLog')) {
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
