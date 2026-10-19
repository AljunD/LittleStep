@props(['placeholder' => 'Search...', 'keep' => []])
{{-- GET search form for index pages. Filter fields go in the slot; $keep holds query values to carry over (e.g. the active tab). --}}
@php
    $isFiltered = collect(request()->query())
        ->reject(fn ($value, $key) => str_ends_with($key, 'page') || array_key_exists($key, $keep))
        ->filter(fn ($value) => filled($value))
        ->isNotEmpty();
    $clearUrl = url()->current().($keep ? '?'.http_build_query($keep) : '');
@endphp
<form method="GET" action="{{ url()->current() }}" role="search"
      {{ $attributes->class('flex flex-col gap-3 rounded-2xl border border-gray-200/70 bg-white p-3 lg:flex-row lg:items-center') }}>
    @foreach ($keep as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    <label class="relative min-w-0 flex-1">
        <span class="sr-only">Search</span>
        <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder }}"
               maxlength="{{ \App\Http\Requests\IndexSearchRequest::SEARCH_MAX_LENGTH }}"
               class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50/60 pl-10 pr-3 text-sm text-gray-800 placeholder:text-gray-400 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10">
    </label>

    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif

    <div class="flex items-center gap-2">
        <x-button type="submit" icon="search">Search</x-button>
        @if ($isFiltered)
            <x-button variant="secondary" :href="$clearUrl">Clear</x-button>
        @endif
    </div>
</form>
