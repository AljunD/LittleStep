@props(['href' => null, 'icon' => null, 'variant' => 'default'])
@php
    $variants = [
        'default' => 'text-gray-700 hover:bg-gray-100 focus:bg-gray-100',
        'danger' => 'text-red-600 hover:bg-red-50 focus:bg-red-50',
        'success' => 'text-green-700 hover:bg-green-50 focus:bg-green-50',
    ];
    $classes = 'flex h-10 w-full items-center gap-2.5 whitespace-nowrap rounded-lg px-3 text-sm font-medium transition focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 '
        .($variants[$variant] ?? $variants['default']);
@endphp
@if ($href)
    <a href="{{ $href }}" role="menuitem" tabindex="-1" {{ $attributes->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="submit" role="menuitem" tabindex="-1" {{ $attributes->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
