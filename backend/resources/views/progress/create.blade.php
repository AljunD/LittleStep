@extends('components.app')

@section('title', 'Create Evaluation - ' . $domainName)

@section('content')
<div class="max-w-7xl mx-auto my-10 px-4">

    <x-validation-errors />

    <div class="text-center mb-6">
        <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">{{ $domainName }} Domain</h1>
        <p class="text-xs text-gray-500 mt-2 uppercase tracking-widest font-semibold">
            Early Childhood Care and Development (ECCD) Checklist
        </p>
        @if($child)
            <p class="text-sm text-gray-600 mt-3">
                Evaluating: <span class="font-bold">{{ $child->first_name }} {{ $child->last_name }}</span>
            </p>
        @endif

        <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold">
            You are filling:
            <span class="bg-blue-600 text-white px-2 py-0.5 rounded">
                {{ $currentRound }}{{ $currentRound == 1 ? 'st' : ($currentRound == 2 ? 'nd' : 'rd') }} Evaluation
            </span>
        </div>
    </div>

    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
        <strong>Reminder:</strong>
        @if($maxAllowedRound === 1)
            The <strong>2nd and 3rd Evaluation</strong> columns are disabled until you complete the 
            <strong>1st Evaluation of all 7 domains</strong>.
        @elseif($maxAllowedRound === 2)
            The <strong>3rd Evaluation</strong> column is disabled until you complete the 
            <strong>2nd Evaluation of all 7 domains</strong>.
        @else
            All evaluation rounds are now available.
        @endif
    </div>

    <form action="{{ route('progress.store') }}" method="POST" id="evaluation-form">
        @csrf
        <input type="hidden" name="child_id" value="{{ $child?->id }}">
        <input type="hidden" name="domain" value="{{ $domainKey }}">

        <div class="mb-6 max-w-sm">
            <label for="assessment_date" class="block text-sm font-bold text-gray-700 mb-1">
                Assessment date ({{ $currentRound }}{{ $currentRound === 1 ? 'st' : ($currentRound === 2 ? 'nd' : 'rd') }} evaluation)
            </label>
            <input
                type="date"
                id="assessment_date"
                name="assessment_date"
                value="{{ old('assessment_date', $assessmentDate) }}"
                min="{{ $child?->date_of_birth }}"
                max="{{ now()->toDateString() }}"
                required
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring focus:ring-blue-200"
            >
            @error('assessment_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($child)
            <div id="live-preview" data-preview-url="{{ route('progress.preview') }}" aria-live="polite"
                 class="mb-6 rounded-xl border border-blue-100 bg-blue-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-blue-900">Live score preview · {{ $domainName }}</h2>
                    <span id="preview-status" class="text-xs font-medium text-blue-700">Loading preview...</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center">
                    @foreach ([
                        'preview-answered' => 'Items answered',
                        'preview-raw' => 'Raw score (✓)',
                        'preview-scaled' => 'Scaled score',
                        'preview-standard' => 'Standard score',
                    ] as $previewId => $previewLabel)
                        <div class="rounded-lg bg-white p-3 shadow-sm">
                            <p id="{{ $previewId }}" class="text-2xl font-black text-gray-900">—</p>
                            <p class="text-[11px] font-semibold uppercase text-gray-500">{{ $previewLabel }}</p>
                        </div>
                    @endforeach
                    <div class="col-span-2 md:col-span-1 rounded-lg bg-white p-3 shadow-sm flex flex-col justify-center">
                        <p id="preview-interpretation" class="text-sm font-bold text-[#001f5b] dark:text-blue-300">—</p>
                        <p class="text-[11px] font-semibold uppercase text-gray-500">Domain interpretation</p>
                    </div>
                </div>

                <p id="preview-overall" class="mt-4 text-sm text-blue-900"></p>
                <ul id="preview-warnings" class="hidden mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 space-y-1"></ul>

                <details class="mt-3">
                    <summary class="cursor-pointer text-sm font-semibold text-blue-800">How is this computed?</summary>
                    <ol id="preview-steps" class="mt-2 space-y-1 text-sm text-gray-700 list-decimal list-inside"></ol>
                </details>
            </div>
        @endif

        <div class="bg-white shadow-md border border-gray-300 overflow-hidden rounded-md">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-800">
                            <th colspan="2" class="border border-gray-300 px-4 py-3 text-sm font-bold uppercase text-left">{{ $domainName }}</th>
                            <th class="border border-gray-300 px-4 py-3 text-sm font-bold uppercase text-left w-1/3">Material / Procedure</th>
                            <th class="border border-gray-300 px-2 py-3 text-sm font-bold uppercase text-center">Present</th>
                            <th class="border border-gray-300 px-4 py-3 text-sm font-bold uppercase text-left">Comments</th>
                        </tr>
                        <tr class="bg-gray-100 text-[10px] font-bold uppercase text-gray-600 border-b border-gray-300">
                            <th colspan="3" class="border-r border-gray-300"></th>
                            <th class="border-r border-gray-300 p-0">
                                <div class="flex text-center border-b border-gray-300">
                                    <div class="w-24 border-r border-gray-300 py-1 {{ $currentRound === 1 ? 'bg-blue-100 text-blue-700' : '' }}">
                                        1st Eval @if(in_array(1, $completedRounds)) <span class="text-green-600">✓</span> @endif
                                    </div>
                                    <div class="w-24 border-r border-gray-300 py-1 {{ $currentRound === 2 ? 'bg-blue-100 text-blue-700' : '' }}">
                                        2nd Eval @if(in_array(2, $completedRounds)) <span class="text-green-600">✓</span> @endif
                                    </div>
                                    <div class="w-24 py-1 {{ $currentRound === 3 ? 'bg-blue-100 text-blue-700' : '' }}">
                                        3rd Eval @if(in_array(3, $completedRounds)) <span class="text-green-600">✓</span> @endif
                                    </div>
                                </div>
                                <div class="flex text-[9px] bg-white">
                                    <div class="w-12 border-r border-gray-200 py-1 text-green-600">✓</div>
                                    <div class="w-12 border-r border-gray-300 py-1 text-gray-400">—</div>
                                    <div class="w-12 border-r border-gray-200 py-1 text-green-600">✓</div>
                                    <div class="w-12 border-r border-gray-300 py-1 text-gray-400">—</div>
                                    <div class="w-12 border-r border-gray-200 py-1 text-green-600">✓</div>
                                    <div class="w-12 py-1 text-gray-400">—</div>
                                </div>
                            </th>
                            <th class="bg-gray-50"></th>
                        </tr>
                    </thead>

                    <tbody class="text-sm">
                        @forelse($items as $index => $item)
                            @php
                                $saved1 = $savedResults[1][$item->id] ?? null;
                                $saved2 = $savedResults[2][$item->id] ?? null;
                                $saved3 = $savedResults[3][$item->id] ?? null;

                                $restoreInput = session()->hasOldInput();
                                $present1 = $restoreInput && $currentRound === 1 ? old("results.{$item->id}.1") : ($saved1['present'] ?? null);
                                $present2 = $restoreInput && $currentRound === 2 ? old("results.{$item->id}.2") : ($saved2['present'] ?? null);
                                $present3 = $restoreInput && $currentRound === 3 ? old("results.{$item->id}.3") : ($saved3['present'] ?? null);

                                $savedComment = $restoreInput
                                    ? old("comments.{$item->id}", '')
                                    : ($savedResults[$currentRound][$item->id]['comments'] ?? '');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-4 text-center font-bold w-10 border border-gray-300">{{ $index + 1 }}.</td>
                                <td class="px-4 py-4 font-semibold text-gray-800 border border-gray-300">{{ $item->activity }}</td>
                                <td class="px-4 py-4 italic text-gray-600 border border-gray-300 text-xs">
                                    {!! nl2br(e($item->materials_and_procedure)) !!}
                                </td>
                                <td class="p-0 border border-gray-300 bg-white">
                                    <div class="flex h-full min-h-[80px]">

                                        <div class="w-12 border-r border-gray-200 flex items-center justify-center {{ $currentRound !== 1 ? 'bg-gray-100 opacity-50' : '' }}">
                                            <input type="checkbox" name="results[{{ $item->id }}][1]" value="check"
                                                   class="score-check" data-eval="1"
                                                   {{ $currentRound !== 1 ? 'disabled' : '' }}
                                                   {{ $present1 === 'check' ? 'checked' : '' }}>
                                        </div>
                                        <div class="w-12 border-r border-gray-300 flex items-center justify-center {{ $currentRound !== 1 ? 'bg-gray-100 opacity-50' : 'bg-gray-50' }}">
                                            <input type="checkbox" name="results[{{ $item->id }}][1]" value="hyphen"
                                                   class="score-hyphen" data-eval="1"
                                                   {{ $currentRound !== 1 ? 'disabled' : '' }}
                                                   {{ $present1 === 'hyphen' ? 'checked' : '' }}>
                                        </div>

                                        <div class="w-12 border-r border-gray-200 flex items-center justify-center {{ $currentRound !== 2 ? 'bg-gray-100 opacity-50' : '' }}">
                                            <input type="checkbox" name="results[{{ $item->id }}][2]" value="check"
                                                   class="score-check" data-eval="2"
                                                   {{ $currentRound !== 2 ? 'disabled' : '' }}
                                                   {{ $present2 === 'check' ? 'checked' : '' }}>
                                        </div>
                                        <div class="w-12 border-r border-gray-300 flex items-center justify-center {{ $currentRound !== 2 ? 'bg-gray-100 opacity-50' : 'bg-gray-50' }}">
                                            <input type="checkbox" name="results[{{ $item->id }}][2]" value="hyphen"
                                                   class="score-hyphen" data-eval="2"
                                                   {{ $currentRound !== 2 ? 'disabled' : '' }}
                                                   {{ $present2 === 'hyphen' ? 'checked' : '' }}>
                                        </div>

                                        <div class="w-12 border-r border-gray-200 flex items-center justify-center {{ $currentRound !== 3 ? 'bg-gray-100 opacity-50' : '' }}">
                                            <input type="checkbox" name="results[{{ $item->id }}][3]" value="check"
                                                   class="score-check" data-eval="3"
                                                   {{ $currentRound !== 3 ? 'disabled' : '' }}
                                                   {{ $present3 === 'check' ? 'checked' : '' }}>
                                        </div>
                                        <div class="w-12 flex items-center justify-center {{ $currentRound !== 3 ? 'bg-gray-100 opacity-50' : 'bg-gray-50' }}">
                                            <input type="checkbox" name="results[{{ $item->id }}][3]" value="hyphen"
                                                   class="score-hyphen" data-eval="3"
                                                   {{ $currentRound !== 3 ? 'disabled' : '' }}
                                                   {{ $present3 === 'hyphen' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 border border-gray-300">
                                    <textarea name="comments[{{ $item->id }}]" maxlength="5000"
                                              class="w-full h-20 border border-gray-300 rounded px-2 py-1 text-sm focus:ring focus:ring-blue-200 resize-none"
                                              placeholder="Add observations..."
                                              {{ $currentRound > 3 ? 'disabled' : '' }}>{{ $savedComment }}</textarea>
                                    @error("comments.{$item->id}")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    No checklist items found. Run the seeder first.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="bg-gray-100 font-bold text-gray-800">
                            <td colspan="2" class="border border-gray-300"></td>
                            <td class="px-6 py-4 text-right uppercase tracking-widest text-sm border border-gray-300">Total Score</td>
                            <td class="p-0 border border-gray-300">
                                <div class="flex h-full text-center text-lg">
                                    <div class="w-24 border-r border-gray-300 py-3" id="totalEval1">0</div>
                                    <div class="w-24 border-r border-gray-300 py-3" id="totalEval2">0</div>
                                    <div class="w-24 py-3" id="totalEval3">0</div>
                                </div>
                            </td>
                            <td class="border border-gray-300"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center mt-10">
            <p class="text-[10px] italic text-gray-500">
                Only the current evaluation round is editable. Previous rounds show the saved answers (read-only).
            </p>
            <div class="flex flex-wrap gap-3">
                <x-button variant="secondary" :href="route('progress.select-domain', ['child_id' => $child?->id])">Cancel</x-button>
                <x-button type="submit" icon="save">
                    Save {{ $currentRound }}{{ $currentRound == 1 ? 'st' : ($currentRound == 2 ? 'nd' : 'rd') }} Evaluation
                </x-button>
            </div>
        </div>
    </form>
</div>

<script nonce="{{ request()->attributes->get('cspNonce') }}">
    function updateScores() {
        document.getElementById('totalEval1').textContent = document.querySelectorAll('.score-check[data-eval="1"]:checked').length;
        document.getElementById('totalEval2').textContent = document.querySelectorAll('.score-check[data-eval="2"]:checked').length;
        document.getElementById('totalEval3').textContent = document.querySelectorAll('.score-check[data-eval="3"]:checked').length;
    }

    document.querySelectorAll('.score-check, .score-hyphen').forEach(cb => {
        cb.addEventListener('change', function () {
            if (this.disabled) return;
            const evalNum = this.dataset.eval;
            const row = this.closest('tr');
            row.querySelectorAll(`[data-eval="${evalNum}"]`).forEach(other => {
                if (other !== this) other.checked = false;
            });
            updateScores();
        });
    });

    document.addEventListener('DOMContentLoaded', updateScores);

    (() => {
        const panel = document.getElementById('live-preview');
        const form = document.getElementById('evaluation-form');
        if (!panel || !form) return;

        const savedHint = 'Not saved yet — updates as you mark items.';
        const setText = (id, value) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value ?? '—';
        };
        const renderList = (id, lines) => {
            const list = document.getElementById(id);
            if (!list) return;
            list.replaceChildren(...lines.map((line) => {
                const item = document.createElement('li');
                item.textContent = line;
                return item;
            }));
            if (id === 'preview-warnings') list.classList.toggle('hidden', lines.length === 0);
        };

        let timer = null;
        let controller = null;

        const refreshPreview = () => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                controller?.abort();
                controller = new AbortController();
                setText('preview-status', 'Updating...');

                try {
                    const response = await fetch(panel.dataset.previewUrl, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                        signal: controller.signal,
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        setText('preview-status', data.message ?? 'Preview unavailable.');
                        return;
                    }

                    const domain = data.domain;
                    const explanation = data.explanation;
                    setText('preview-answered', `${domain.items_answered}/${domain.items_total}`);
                    setText('preview-raw', domain.raw_score);
                    setText('preview-scaled', domain.scaled_score);
                    setText('preview-interpretation', domain.interpretation ?? 'Answer every item to score this domain');
                    setText('preview-standard', explanation.standard_score);
                    setText('preview-overall', explanation.standard_score !== null
                        ? `Overall (round ${data.round}): ${explanation.overall}`
                        : `${explanation.scored_domains} of 7 domains scored in round ${data.round}. The standard score appears once all 7 are scored.`);
                    renderList('preview-warnings', explanation.warnings);
                    renderList('preview-steps', explanation.steps);
                    setText('preview-status', savedHint);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        setText('preview-status', 'Preview unavailable right now. Saving still works.');
                    }
                }
            }, 250);
        };

        form.addEventListener('change', refreshPreview);
        refreshPreview();
    })();
</script>
@endsection