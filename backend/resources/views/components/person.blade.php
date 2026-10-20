@props(['name', 'subtitle' => null])
@php
    $name = trim(preg_replace('/\s+/', ' ', (string) $name));
    $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $palette = ['bg-sky-100 text-sky-700', 'bg-violet-100 text-violet-700', 'bg-amber-100 text-amber-700', 'bg-emerald-100 text-emerald-700', 'bg-rose-100 text-rose-700'];
    // Masked to 31 bits: crc32() is negative for some names on 32-bit PHP (Wasmer Edge), which gave index -1.
    $color = $palette[(crc32($name) & 0x7FFFFFFF) % count($palette)];
@endphp
<div {{ $attributes->class('flex items-center gap-3') }}>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $color }}" aria-hidden="true">{{ $initials ?: '?' }}</span>
    <div class="min-w-0">
        <p class="truncate font-bold text-gray-900">{{ $name }}</p>
        @if (filled($subtitle))
            <p class="truncate text-xs text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
</div>
