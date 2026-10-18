{{--
    ECCD Checklist, Child's Record 2 (20 pages). Each page is the official form (resources/pdf/childs-record-2)
    with the child's data positioned on top by App\Services\ChildsRecord2Report, in points on a 612 × 792 page.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ECCD Checklist, Child's Record 2</title>
    <style>
        @page { size: 612pt 792pt; margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: "DejaVu Sans", sans-serif; color: #10204f; }
        .page { position: relative; width: 612pt; height: 791pt; overflow: hidden; page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .template, .overlay { position: absolute; left: 0; top: 0; width: 612pt; height: 792pt; }
        .field { position: absolute; overflow: hidden; }
        /* Single-line values are sized to their cell; clipping them would cut descenders (y, g, p). */
        .nowrap { white-space: nowrap; overflow: visible; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
@foreach ($pages as $page)
    <div class="page">
        <img class="template" src="{{ $page['template'] }}" alt="">
        @if ($page['svg'])
            <img class="overlay" src="{{ $page['svg'] }}" alt="">
        @endif
        @foreach ($page['items'] as $item)
            @if ($item['text'] !== '')
                <div class="field{{ ($item['nowrap'] ?? false) ? ' nowrap' : '' }}{{ ($item['bold'] ?? false) ? ' bold' : '' }}"
                     style="left: {{ $item['left'] }}pt; top: {{ $item['top'] }}pt; width: {{ $item['width'] }}pt; height: {{ $item['height'] }}pt; font-size: {{ $item['size'] }}pt; line-height: {{ $item['lineHeight'] ?? $item['size'] * 1.25 }}pt; text-align: {{ $item['align'] }};">{!! nl2br(e($item['text'])) !!}</div>
            @endif
        @endforeach
    </div>
@endforeach
</body>
</html>
