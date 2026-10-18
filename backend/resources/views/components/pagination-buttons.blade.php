@props(['paginator'])
<div {{ $attributes->class('flex gap-2') }}>
    @if ($paginator->onFirstPage())
        <x-button variant="secondary" icon="back" disabled>Previous</x-button>
    @else
        <x-button variant="secondary" icon="back" :href="$paginator->previousPageUrl()">Previous</x-button>
    @endif

    @if ($paginator->hasMorePages())
        <x-button variant="secondary" :href="$paginator->nextPageUrl()">Next <x-icon name="next" /></x-button>
    @else
        <x-button variant="secondary" disabled>Next <x-icon name="next" /></x-button>
    @endif
</div>
