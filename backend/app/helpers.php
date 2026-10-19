<?php

use App\Support\AuditLog;

if (! function_exists('recordLog')) {
    /** Writes an audit log entry (see App\Support\AuditLog). Pass null as the entity id when there is no single record. */
    function recordLog(string $action, string $entityType, ?int $entityId = null, ?string $details = null, ?int $userId = null): void
    {
        AuditLog::record($action, $entityType, $entityId, $details, $userId);
    }
}
