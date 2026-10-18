{{--
    Small "Edit" link next to a section heading on the progress record page. It jumps to the matching
    section of the edit form (progress.edit#{section}).
--}}
@props(['href', 'label'])
<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition']) }}
   aria-label="Edit {{ $label }}">
    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4l10.5-10.5a2.5 2.5 0 00-3.536-3.536L4 16.5V20z"></path>
    </svg>
    Edit
</a>
