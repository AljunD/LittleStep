@props(['name', 'label', 'type' => 'text', 'value' => null, 'options' => null, 'hint' => null])
{{-- Labeled settings form control with its validation message. type: text/tel/password/..., "textarea", or pass $options for a select. Password values are never rendered. --}}
@php
    $hasError = $errors->has($name);
    $describedBy = trim(($hint ? $name.'-hint ' : '').($hasError ? $name.'-error' : ''));
    $fieldClass = 'mt-1 w-full rounded-xl border bg-gray-50/60 px-3 text-sm text-gray-800 transition focus:bg-white focus:outline-none focus:ring-4 '
        .($hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-500/10' : 'border-gray-200 focus:border-blue-500 focus:ring-blue-500/10');
    $state = new \Illuminate\View\ComponentAttributeBag(array_filter([
        'aria-invalid' => $hasError ? 'true' : null,
        'aria-describedby' => $describedBy ?: null,
    ]));
@endphp
<div>
    <label for="{{ $name }}" class="text-sm font-semibold text-gray-700">{{ $label }}</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" {{ $attributes }} {{ $state }} class="{{ $fieldClass }} py-2.5">{{ $value }}</textarea>
    @elseif (is_array($options))
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes }} {{ $state }} class="{{ $fieldClass }} h-10 pr-8">
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" @if ($type !== 'password') value="{{ $value }}" @endif
               {{ $attributes }} {{ $state }} class="{{ $fieldClass }} h-10">
    @endif
    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
    @enderror
</div>
