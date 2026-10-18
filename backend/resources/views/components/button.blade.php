@props(['variant' => 'primary', 'href' => null, 'icon' => null, 'type' => 'button'])
@php
    $variants = [
        'primary' => 'border-transparent bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus-visible:ring-blue-500',
        'secondary' => 'border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 hover:text-gray-900 focus-visible:ring-blue-500',
        'danger' => 'border-transparent bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:ring-red-500',
    ];
    $classes = 'inline-flex h-10 shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl border px-4 text-sm font-semibold transition '
        .'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 active:scale-[.98] '
        .'disabled:cursor-not-allowed disabled:opacity-60 disabled:active:scale-100 '
        .($variants[$variant] ?? $variants['primary']);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
