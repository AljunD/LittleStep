@props(['name', 'value' => null])
@php
    $levels = \App\Support\FormRules::EDUCATION_LEVELS;
    if (is_string($value) && $value !== '' && ! in_array($value, $levels, true)) {
        $levels[] = $value;
    }
@endphp
<select name="{{ $name }}" {{ $attributes }}>
    <option value="">Select Educational Attainment</option>
    @foreach ($levels as $level)
        <option value="{{ $level }}" @selected($value === $level)>{{ $level }}</option>
    @endforeach
</select>
