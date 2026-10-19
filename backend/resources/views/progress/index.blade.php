@extends('components.app')

@section('title', 'Progress Records')

@section('content')
<div class="space-y-6">
    <x-page-header title="Progress Records" description="Track each child's ECCD evaluations and observations." />

    <x-search-bar placeholder="Search by child name" />

    <x-table :columns="['Child', 'Evaluation', 'Evaluation Date', 'Status']" :paginator="$children"
             :empty="request('search') ? 'No children match your search.' : 'No children to evaluate yet.'">
        @foreach($children as $child)
            @php($latestRecord = $child->latest_record)
            <tr>
                <td class="whitespace-nowrap">
                    <x-person :name="$child->first_name.' '.$child->last_name" />
                </td>
                <td class="whitespace-nowrap font-semibold text-gray-800">
                    {{ $child->current_evaluation_round ? 'Round '.$child->current_evaluation_round.' of 3' : '—' }}
                </td>
                <td class="whitespace-nowrap">{{ $child->current_round_date ?? '—' }}</td>
                <td class="whitespace-nowrap">
                    <x-progress-status :status="$child->current_round_status" :completed="$child->completed_domains_count" />
                </td>
                <td class="whitespace-nowrap text-right">
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
        @endforeach
    </x-table>
</div>
@endsection
