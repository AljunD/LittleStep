@extends('components.app')

@section('title', 'Progress Records')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Progress Records</h1>
        <p class="text-sm text-gray-500">View all child progress evaluations.</p>
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
                            $latestRecord = $child->progressRecords->first();
                        @endphp
                        <tr class="hover:bg-blue-50 transition-colors group">
                            <td class="px-6 py-5 text-sm text-gray-700">{{ $child->id }}</td>
                            <td class="px-6 py-5 text-sm font-bold text-gray-900">
                                {{ $child->first_name }} {{ $child->last_name }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-700">
                                {{ $latestRecord && $latestRecord->teacher ? $latestRecord->teacher->first_name . ' ' . $latestRecord->teacher->last_name : 'N/A' }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-700">
                                {{ $latestRecord ? $latestRecord->evaluation_number : '-' }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-700">
                                {{ $latestRecord ? \Carbon\Carbon::parse($latestRecord->evaluation_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-6 py-5 text-sm">
                                @if($latestRecord && $latestRecord->status === 'completed')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Completed
                                    </span>
                                @elseif($latestRecord && $latestRecord->status === 'in_progress')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        In Progress
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-right">
                                <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <!-- View Progress -->
                                    <a href="{{ route('progress.show', ['id' => $latestRecord ? $latestRecord->id : $child->id]) }}" 
                                       class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                        View
                                    </a>
                                    <!-- Add Evaluation -->
                                    <a href="{{ route('progress.select-domain', ['child_id' => $child->id]) }}" 
                                       class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold hover:bg-purple-200 transition">
                                        + Add Evaluation
                                    </a>
                                    <!-- Add Observation -->
                                    <a href="{{ route('progress.add-observation', ['child_id' => $child->id]) }}" 
                                       class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold hover:bg-purple-200 transition">
                                        + Add Observation
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No children records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
            <p class="text-gray-500">
                Showing Page {{ $children->currentPage() }} of {{ $children->lastPage() ?: 1 }}
            </p>
            <div class="flex space-x-2">
                @if ($children->onFirstPage())
                    <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed" disabled>Previous</button>
                @else
                    <a href="{{ $children->previousPageUrl() }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition">Previous</a>
                @endif

                @if ($children->hasMorePages())
                    <a href="{{ $children->nextPageUrl() }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition">Next</a>
                @else
                    <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed" disabled>Next</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection