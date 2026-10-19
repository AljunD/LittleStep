@props(['name', 'label', 'type' => 'text', 'options' => null, 'placeholder' => null])
{{-- One filter control for <x-search-bar>: a select when $options is given (submits on change), otherwise an input. --}}
@php
    $value = (string) request($name, '');
    $fieldClass = 'h-10 rounded-xl border border-gray-200 bg-gray-50/60 px-3 text-sm text-gray-700 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10';
@endphp
<label class="flex items-center gap-2">
    <span class="{{ $type === 'date' ? 'text-xs font-semibold text-gray-500' : 'sr-only' }}">{{ $label }}</span>
    @if (is_iterable($options))
        <select name="{{ $name }}" data-auto-submit {{ $attributes->class($fieldClass.' pr-8') }}>
            <option value="">{{ $placeholder ?? 'All' }}</option>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected($value === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" {{ $attributes->class($fieldClass) }}>
    @endif
</label>
