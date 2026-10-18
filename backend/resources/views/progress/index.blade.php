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
                                <x-action-menu label="Actions for {{ $child->first_name }} {{ $child->last_name }}">
                                    @if($latestRecord)
                                        <x-action-menu.item :href="route('progress.show', $latestRecord->id)" icon="view">View Record</x-action-menu.item>
                                        <x-action-menu.item :href="route('progress.edit', $latestRecord->id)" icon="edit">Edit Record</x-action-menu.item>
                                    @endif
                                    <x-action-menu.item :href="route('progress.select-domain', ['child_id' => $child->id])" icon="evaluate">Add Evaluation</x-action-menu.item>
                                    <x-action-menu.item :href="route('progress.add-observation', ['child_id' => $child->id])" icon="observation">Add Observation</x-action-menu.item>
                                </x-action-menu>
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
                <x-pagination-buttons :paginator="$children" />
            </div>
        @endif
    </div>
</div>
@endsection