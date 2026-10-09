@extends('components.app')

@section('title', 'Create Evaluation - ' . $domainName . ' Domain')

@section('content')
<div class="max-w-7xl mx-auto my-10 px-4">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">
            {{ $domainName }} Domain
        </h1>
        <p class="text-xs text-gray-500 mt-2 uppercase tracking-widest font-semibold">
            Early Childhood Care and Development (ECCD) Checklist, Child’s Record 2
        </p>
        @if($child)
            <p class="text-sm text-gray-600 mt-2">
                Evaluating: <span class="font-bold">{{ $child->first_name }} {{ $child->last_name }}</span>
            </p>
        @endif
    </div>

    <!-- Success Message -->
    <div id="success-message" class="hidden p-12 text-center">
        <div class="max-w-md mx-auto">
            <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Evaluation Saved Successfully</h3>
            <p class="text-gray-600 mb-8">The evaluation record has been saved and linked to the child profile.</p>
            <a href="{{ route('progress.select-domain', ['child_id' => $child?->id]) }}" 
               class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                Back to Domain Selection
            </a>
        </div>
    </div>

    <!-- Failed Message -->
    <div id="failed-message" class="hidden p-12 text-center">
        <div class="max-w-md mx-auto">
            <div class="mb-6 bg-red-100 text-red-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✕</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Evaluation Save Failed</h3>
            <p class="text-gray-600 mb-8">Something went wrong while saving the evaluation. Please try again.</p>
            <a href="{{ route('progress.select-domain', ['child_id' => $child?->id]) }}" 
               class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                Back to Domain Selection
            </a>
        </div>
    </div>

    <!-- Evaluation Form -->
    <div class="bg-white shadow-md border border-gray-300 overflow-hidden rounded-md" id="evaluation-form">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse" id="domainTable">
                <thead>
                    <tr class="bg-gray-200 text-gray-800">
                        <th colspan="2" class="border border-gray-300 px-4 py-3 text-sm font-bold uppercase text-left">
                            {{ $domainName }}
                        </th>
                        <th class="border border-gray-300 px-4 py-3 text-sm font-bold uppercase text-left w-1/3">
                            Material / Procedure
                        </th>
                        <th class="border border-gray-300 px-2 py-3 text-sm font-bold uppercase text-center">
                            Present
                        </th>
                        <th class="border border-gray-300 px-4 py-3 text-sm font-bold uppercase text-left">
                            Comments
                        </th>
                    </tr>
                    <tr class="bg-gray-100 text-[10px] font-bold uppercase text-gray-600 border-b border-gray-300">
                        <th colspan="3" class="border-r border-gray-300"></th>
                        <th class="border-r border-gray-300 p-0">
                            <div class="flex text-center border-b border-gray-300">
                                <div class="w-24 border-r border-gray-300 py-1">1st Eval</div>
                                <div class="w-24 border-r border-gray-300 py-1">2nd Eval</div>
                                <div class="w-24 py-1">3rd Eval</div>
                            </div>
                            <div class="flex text-[9px] bg-white">
                                <div class="w-12 border-r border-gray-200 py-1 text-green-600">Check</div>
                                <div class="w-12 border-r border-gray-300 py-1 text-gray-400">Hyphen</div>
                                <div class="w-12 border-r border-gray-200 py-1 text-green-600">Check</div>
                                <div class="w-12 border-r border-gray-300 py-1 text-gray-400">Hyphen</div>
                                <div class="w-12 border-r border-gray-200 py-1 text-green-600">Check</div>
                                <div class="w-12 py-1 text-gray-400">Hyphen</div>
                            </div>
                        </th>
                        <th class="bg-gray-50"></th>
                    </tr>
                </thead>
                
                <tbody class="text-sm">
                    @forelse($items as $index => $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-2 py-4 text-center font-bold w-10 border border-gray-300">
                            {{ $index + 1 }}.
                        </td>
                        <td class="px-4 py-4 font-semibold text-gray-800 border border-gray-300">
                            {{ $item->activity }}
                        </td>
                        <td class="px-4 py-4 italic text-gray-600 border border-gray-300 text-xs">
                            {!! nl2br(e($item->materials_and_procedure)) !!}
                        </td>
                        <td class="p-0 border border-gray-300 bg-white">
                            <div class="flex h-full min-h-[80px]">
                                <!-- 1st Eval -->
                                <div class="w-12 border-r border-gray-200 flex items-center justify-center">
                                    <input type="checkbox" class="score-check" data-eval="1" data-item="{{ $item->id }}">
                                </div>
                                <div class="w-12 border-r border-gray-300 flex items-center justify-center bg-gray-50">
                                    <input type="checkbox" class="score-hyphen" data-eval="1" data-item="{{ $item->id }}">
                                </div>
                                <!-- 2nd Eval -->
                                <div class="w-12 border-r border-gray-200 flex items-center justify-center">
                                    <input type="checkbox" class="score-check" data-eval="2" data-item="{{ $item->id }}">
                                </div>
                                <div class="w-12 border-r border-gray-300 flex items-center justify-center bg-gray-50">
                                    <input type="checkbox" class="score-hyphen" data-eval="2" data-item="{{ $item->id }}">
                                </div>
                                <!-- 3rd Eval -->
                                <div class="w-12 border-r border-gray-200 flex items-center justify-center">
                                    <input type="checkbox" class="score-check" data-eval="3" data-item="{{ $item->id }}">
                                </div>
                                <div class="w-12 flex items-center justify-center bg-gray-50">
                                    <input type="checkbox" class="score-hyphen" data-eval="3" data-item="{{ $item->id }}">
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 border border-gray-300">
                            <textarea class="w-full h-20 border border-gray-300 rounded px-2 py-1 text-sm focus:ring focus:ring-blue-200 resize-none comments-field"
                                      placeholder="Add observations..."
                                      data-item="{{ $item->id }}"></textarea>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No checklist items found for this domain. Please run the ECCDChecklistSeeder.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                <!-- Total Score Row -->
                <tfoot>
                    <tr class="bg-gray-100 font-bold text-gray-800">
                        <td colspan="2" class="border border-gray-300"></td>
                        <td class="px-6 py-4 text-right uppercase tracking-widest text-sm border border-gray-300">
                            Total Score
                        </td>
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

    <!-- Footer Actions -->
    <div class="flex justify-between items-center mt-10" id="footer-actions">
        <p class="text-[10px] italic text-gray-500 flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Ensure all physical assessments are verified by the primary caregiver.</span>
        </p>
        <div class="flex space-x-4">
            <a href="{{ route('progress.select-domain', ['child_id' => $child?->id]) }}" 
               class="px-8 py-3 text-gray-600 font-bold hover:text-black transition inline-block">
                Cancel
            </a>
            <button type="button" id="save-btn" 
                    class="px-12 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition shadow-md hover:shadow-blue-200 transform active:scale-95">
                Save Evaluation
            </button>
        </div>
    </div>
</div>

<script>
    function updateScores() {
        let totals = {1: 0, 2: 0, 3: 0};
        document.querySelectorAll('.score-check').forEach(cb => {
            if (cb.checked) {
                totals[cb.dataset.eval]++;
            }
        });
        document.getElementById('totalEval1').textContent = totals[1];
        document.getElementById('totalEval2').textContent = totals[2];
        document.getElementById('totalEval3').textContent = totals[3];
    }

    document.querySelectorAll('.score-check, .score-hyphen').forEach(cb => {
        cb.addEventListener('change', function () {
            const evalNum = this.dataset.eval;
            const parentRow = this.closest('tr');

            // Only one checkbox per evaluation per row
            parentRow.querySelectorAll('[data-eval="' + evalNum + '"]').forEach(other => {
                if (other !== this) other.checked = false;
            });

            // Require comments if hyphen is checked
            const commentsField = parentRow.querySelector('.comments-field');
            if (this.classList.contains('score-hyphen') && this.checked) {
                commentsField.setAttribute('required', 'required');
            } else if (this.classList.contains('score-hyphen') && !this.checked) {
                commentsField.removeAttribute('required');
            }

            updateScores();
        });
    });

    // Save button (still simulated for now)
    document.getElementById('save-btn').addEventListener('click', function () {
        const confirmed = confirm("Are you sure you want to save this evaluation?");
        if (!confirmed) return;

        const isSuccess = Math.random() > 0.5; // temporary simulation
        document.getElementById('evaluation-form').classList.add('hidden');
        document.getElementById('footer-actions').classList.add('hidden');

        if (isSuccess) {
            document.getElementById('success-message').classList.remove('hidden');
            document.getElementById('failed-message').classList.add('hidden');
        } else {
            document.getElementById('failed-message').classList.remove('hidden');
            document.getElementById('success-message').classList.add('hidden');
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
@endsection