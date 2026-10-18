@extends('components.app')

@section('title', 'Progress Records')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Progress Records</h1>
        <p class="text-sm text-gray-500">View all child progress evaluations from the database.</p>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Child</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Teacher</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Evaluation #</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Evaluation Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($children as $child)
                        @php
                            $latestRecord = $child->latest_record;
                        @endphp
                        <tr class="hover:bg-blue-50 transition-colors group">
                            <td class="px-6 py-5 text-sm text-gray-700">{{ $child->id }}</td>

                            <td class="px-6 py-5 text-sm font-bold text-gray-900">
                                {{ $child->first_name }} {{ $child->last_name }}
                            </td>

                            <td class="px-6 py-5 text-sm text-gray-700">
                                @if($latestRecord && $latestRecord->teacher)
                                    {{ $latestRecord->teacher->first_name }} {{ $latestRecord->teacher->last_name }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Just the number 1, 2 or 3 --}}
                            <td class="px-6 py-5 text-sm font-semibold text-gray-800">
                                {{ $child->current_evaluation_round ?? '—' }}
                            </td>

                            <td class="px-6 py-5 text-sm text-gray-700">
                                {{ $child->current_round_date ?? '—' }}
                            </td>

                            {{-- Same status and colours as the progress record page --}}
                            <td class="px-6 py-5 text-sm">
                                <x-progress-status :status="$child->current_round_status" :completed="$child->completed_domains_count" />
                            </td>

                            <td class="px-6 py-5 whitespace-nowrap text-right">
                                <div class="flex justify-end space-x-2">
                                    @if($latestRecord)
                                        <a href="{{ route('progress.show', $latestRecord->id) }}"
                                           class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                            View
                                        </a>

                                        <a href="{{ route('progress.edit', $latestRecord->id) }}"
                                           class="px-4 py-2 bg-amber-100 text-amber-700 rounded-lg text-sm font-semibold hover:bg-amber-200 transition">
                                            Edit
                                        </a>
                                    @endif

                                    <a href="{{ route('progress.select-domain', ['child_id' => $child->id]) }}"
                                       class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold hover:bg-purple-200 transition">
                                        + Add Evaluation
                                    </a>

                                    <a href="{{ route('progress.add-observation', ['child_id' => $child->id]) }}"
                                       class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-semibold hover:bg-indigo-200 transition">
                                        + Add Observation
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                No children found in the database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($children->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
                <p class="text-gray-500">
                    Showing page {{ $children->currentPage() }} of {{ $children->lastPage() }}
                </p>
                <div class="flex space-x-2">
                    @if ($children->onFirstPage())
                        <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed" disabled>
                            Previous
                        </button>
                    @else
                        <a href="{{ $children->previousPageUrl() }}"
                           class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition">
                            Previous
                        </a>
                    @endif

                    @if ($children->hasMorePages())
                        <a href="{{ $children->nextPageUrl() }}"
                           class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition">
                            Next
                        </a>
                    @else
                        <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed" disabled>
                            Next
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection