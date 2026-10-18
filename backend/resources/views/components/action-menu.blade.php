@props(['label' => 'Actions'])
<div {{ $attributes->class('relative inline-flex') }} data-action-menu>
    <button type="button" data-action-menu-trigger aria-haspopup="menu" aria-expanded="false" aria-label="{{ $label }}" title="{{ $label }}"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 aria-expanded:bg-gray-100 aria-expanded:text-gray-900">
        <x-icon name="dots" class="h-5 w-5" />
    </button>
    <div data-action-menu-panel role="menu" aria-label="{{ $label }}" hidden
         class="fixed z-50 min-w-52 rounded-xl border border-gray-200 bg-white p-1.5 text-left shadow-lg">
        {{ $slot }}
    </div>
</div>
