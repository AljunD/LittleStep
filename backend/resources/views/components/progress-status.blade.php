@props(['status', 'completed' => 0, 'round' => null])
@php
    $ordinal = [1 => '1st', 2 => '2nd', 3 => '3rd'][$round] ?? null;
@endphp
<div {{ $attributes }}>
    <x-status-badge :status="$status" :prefix="$ordinal ? $ordinal.' Evaluation · ' : null" />
    <div class="mt-1 text-[11px] text-gray-500">{{ $completed }}/7 domains completed</div>
</div>
