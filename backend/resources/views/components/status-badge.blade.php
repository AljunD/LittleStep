@props(['status', 'prefix' => null])
@php
    [$color, $label] = match ($status) {
        'completed' => ['green', 'Completed'],
        'in_progress' => ['amber', 'In Progress'],
        default => ['gray', 'Pending'],
    };
@endphp
<x-badge :color="$color" dot {{ $attributes }}>{{ $prefix }}{{ $label }}</x-badge>
