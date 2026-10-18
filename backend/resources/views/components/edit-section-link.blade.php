{{--
    "Edit" button next to a section heading on the progress record page. It jumps to the matching
    section of the edit form (progress.edit#{section}).
--}}
@props(['href', 'label'])
<x-button variant="secondary" icon="edit" :href="$href" aria-label="Edit {{ $label }}" {{ $attributes }}>Edit</x-button>
