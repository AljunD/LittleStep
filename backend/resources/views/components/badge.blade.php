@props(['color' => 'gray', 'dot' => false])
@php
    $colors = [
        'gray' => ['bg-gray-100 text-gray-600', 'bg-gray-400'],
        'blue' => ['bg-blue-50 text-blue-700', 'bg-blue-500'],
        'sky' => ['bg-sky-50 text-sky-700', 'bg-sky-500'],
        'green' => ['bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
        'amber' => ['bg-amber-50 text-amber-700', 'bg-amber-500'],
        'violet' => ['bg-violet-50 text-violet-700', 'bg-violet-500'],
        'red' => ['bg-red-50 text-red-700', 'bg-red-500'],
    ];
    [$classes, $dotClass] = $colors[$color] ?? $colors['gray'];
@endphp
<span {{ $attributes->class('inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold '.$classes) }}>@if ($dot)<span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>@endif{{ $slot }}</span>
