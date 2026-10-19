@props(['action'])
<x-badge :color="\App\Support\AuditLog::actionColor($action)" {{ $attributes }}>{{ \App\Support\AuditLog::actionLabel($action) }}</x-badge>
