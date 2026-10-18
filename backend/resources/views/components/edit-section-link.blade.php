@props(['href', 'label'])
<x-button variant="secondary" icon="edit" :href="$href" aria-label="Edit {{ $label }}" {{ $attributes }}>Edit</x-button>
