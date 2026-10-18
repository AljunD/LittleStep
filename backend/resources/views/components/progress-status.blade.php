{{--
    Status of a child's current evaluation (ProgressController::currentRoundProgress), shared by the
    Progress Records list and the progress record page so both show the same status and colour.
--}}
@props(['status', 'completed' => 0, 'round' => null])
@php
    $badge = match ($status) {
        'completed' => ['label' => 'Completed', 'class' => 'bg-green-100 text-green-800 ring-green-600/20', 'dot' => 'bg-green-500'],
        'in_progress' => ['label' => 'In Progress', 'class' => 'bg-amber-100 text-amber-800 ring-amber-600/20', 'dot' => 'bg-amber-500'],
        default => ['label' => 'Pending', 'class' => 'bg-gray-100 text-gray-600 ring-gray-500/20', 'dot' => 'bg-gray-400'],
    };
    $ordinal = [1 => '1st', 2 => '2nd', 3 => '3rd'][$round] ?? null;
@endphp
<div {{ $attributes }}>
    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge['class'] }}">
        <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}" aria-hidden="true"></span>
        @if ($ordinal){{ $ordinal }} Evaluation · @endif{{ $badge['label'] }}
    </span>
    <div class="mt-1 text-[11px] text-gray-500">{{ $completed }}/7 domains completed</div>
</div>
