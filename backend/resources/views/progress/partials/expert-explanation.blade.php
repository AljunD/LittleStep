@php
    $roundLabels = [1 => '1st', 2 => '2nd', 3 => '3rd'];
    $latestExplainedRound = $explanations ? max(array_keys($explanations)) : null;
@endphp

<section class="mt-16" aria-labelledby="expert-explanation-heading" data-expert-explanation>
    <h2 id="expert-explanation-heading" class="text-4xl font-black text-[#001f5b] text-center italic mb-2">How Was This Computed?</h2>
    <p class="text-xs text-gray-700 mb-6 text-center">
        The expert system follows the ECCD Technical Manual step by step. Open an evaluation to see each step.
    </p>

    @forelse ($explanations as $round => $explanation)
        <details class="mb-4 rounded-xl border border-gray-200 bg-white shadow-sm" data-explanation-round="{{ $round }}"
                 @if ($round === $latestExplainedRound) open @endif>
            <summary class="cursor-pointer px-5 py-4 flex flex-wrap items-center justify-between gap-2">
                <span class="font-bold text-gray-900">
                    {{ $roundLabels[$round] }} Evaluation
                    <span class="font-medium text-gray-500">
                        · {{ \Carbon\Carbon::parse($explanation['evaluation_date'])->format('F d, Y') }}
                        @if ($explanation['age']) · Age {{ $explanation['age']['label'] }} @endif
                    </span>
                </span>
                <span class="text-sm font-semibold text-[#001f5b]">
                    @if ($explanation['standard_score'] !== null)
                        Standard score {{ $explanation['standard_score'] }} — {{ $explanation['overall'] }}
                    @else
                        {{ $explanation['scored_domains'] }} of 7 domains scored
                    @endif
                </span>
            </summary>

            <div class="px-5 pb-5 space-y-5">
                @if ($explanation['warnings'])
                    <ul class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 space-y-1">
                        @foreach ($explanation['warnings'] as $warning)
                            <li>⚠ {{ $warning }}</li>
                        @endforeach
                    </ul>
                @endif

                <ol class="space-y-2 text-sm text-gray-700">
                    @foreach ($explanation['steps'] as $step)
                        <li class="flex gap-3">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
                            <span class="pt-0.5">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Domain</th>
                                <th class="px-4 py-3 text-center">Answered</th>
                                <th class="px-4 py-3 text-center">Raw (✓)</th>
                                <th class="px-4 py-3 text-center">Scaled</th>
                                <th class="px-4 py-3 text-left">Interpretation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($explanation['domains'] as $domain)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $domain['label'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $domain['items_answered'] }}/{{ $domain['items_total'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-900">{{ $domain['raw_score'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-900">{{ $domain['scaled_score'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $domain['interpretation'] ?? 'Not scored yet' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @empty
        <p class="text-sm text-gray-500 text-center">No assessment dates are set yet.</p>
    @endforelse

    <div class="mt-10" data-focus-items>
        <h3 class="text-2xl font-black text-[#001f5b] text-center mb-2">Focus Items for the Next Evaluation</h3>
        <p class="text-xs text-gray-700 mb-6 text-center">
            Checklist items marked — (not yet present) in the latest evaluation of each domain.
        </p>

        @if ($skillsToPractice)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($skillsToPractice as $domainSkills)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="font-bold text-gray-900">{{ $domainSkills['label'] }}</p>
                        <p class="text-xs text-gray-500 mb-3">{{ $roundLabels[$domainSkills['round']] }} evaluation · {{ count($domainSkills['items']) }} item(s)</p>
                        <ul class="space-y-1 text-sm text-gray-700">
                            @foreach ($domainSkills['items'] as $item)
                                <li class="flex gap-2">
                                    <span class="text-gray-400 font-semibold">{{ $item['item_number'] ?? '•' }}.</span>
                                    <span>{{ $item['activity'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 text-center">No items are marked — yet.</p>
        @endif
    </div>
</section>
