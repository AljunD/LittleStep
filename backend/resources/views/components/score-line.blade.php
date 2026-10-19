@props(['scores' => [], 'min' => 1, 'max' => 19, 'rows' => true])
{{--
    Connects the x marks of a Child's Record 2 chart, like the "connect the x's" instruction on the form.
    $scores: one score per column (in column order), or null when that column has none; a missing or
    out-of-range score breaks the line instead of being skipped over.
    - rows=true:  one equal-height row per whole score, $max at the top and $min at the bottom (Scaled Scores, 1–19).
    - rows=false: a continuous scale, $max at the top edge and $min at the bottom edge (Standard Scores, 20–160).
    Place it inside the positioned chart container and pass classes that stretch it over the plotting area.

    The SVG sits in a plain <div>: an absolutely positioned <svg> is a replaced element, so left/right/top/bottom
    would not stretch it and it would fall back to its intrinsic size, misaligning the line with the x marks.
--}}
@php
    $columnCount = max(count($scores), 1);
    $rowCount = $max - $min + 1;
    $height = $rows ? $rowCount * 100 : 1000;
    $points = collect(array_values($scores))->map(fn ($score, $column) => is_numeric($score) && $score >= $min && $score <= $max
        ? [
            'x' => ($column + 0.5) * 100,
            'y' => round($rows ? ($max - $score + 0.5) * 100 : ($max - $score) / ($max - $min) * $height, 2),
        ]
        : null);
    $segments = $points->sliding(2)
        ->map(fn ($pair) => $pair->values())
        ->filter(fn ($pair) => $pair[0] && $pair[1])
        ->values();
@endphp
@if ($segments->isNotEmpty())
    <div data-score-line aria-hidden="true" {{ $attributes->class('pointer-events-none absolute') }}>
        <svg class="block h-full w-full" viewBox="0 0 {{ $columnCount * 100 }} {{ $height }}" preserveAspectRatio="none" focusable="false">
            @foreach ($segments as [$from, $to])
                <line x1="{{ $from['x'] }}" y1="{{ $from['y'] }}" x2="{{ $to['x'] }}" y2="{{ $to['y'] }}"
                      stroke="#001f5b" stroke-width="1.5" stroke-linecap="round" vector-effect="non-scaling-stroke" />
            @endforeach
        </svg>
    </div>
@endif
