@extends('components.app')

@section('title', 'System Logs')

@section('content')
@php($formatOption = fn ($value) => ucfirst(str_replace('_', ' ', $value)))
<div class="space-y-6">
    <x-page-header title="System Logs" description="View actions recorded from your account." />

    <x-search-bar placeholder="Search log details">
        <x-filter-field name="action" label="Action" placeholder="All actions"
                        :options="$actions->mapWithKeys(fn ($action) => [$action => $formatOption($action)])" />
        <x-filter-field name="entity_type" label="Entity" placeholder="All entities"
                        :options="$entityTypes->mapWithKeys(fn ($type) => [$type => $formatOption($type)])" />
        <x-filter-field type="date" name="from_date" label="From" />
        <x-filter-field type="date" name="to_date" label="To" />
    </x-search-bar>

    <x-table :columns="['Action', 'Entity', 'Details', 'Date']" :actions="false" :paginator="$logs"
             :empty="request()->anyFilled(['search', 'action', 'entity_type', 'from_date', 'to_date']) ? 'No logs match your filters.' : 'No activity recorded yet.'">
        @foreach($logs as $log)
            <tr>
                <td class="whitespace-nowrap"><x-log-action :action="$log->action" /></td>
                <td class="whitespace-nowrap font-semibold text-gray-800">
                    {{ $formatOption($log->entity_type) }}@if($log->entity_id) <span class="font-normal text-gray-400">#{{ $log->entity_id }}</span>@endif
                </td>
                <td class="max-w-md truncate" title="{{ $log->details }}">{{ $log->details }}</td>
                <td class="whitespace-nowrap">
                    <div class="text-gray-800">{{ $log->created_at->format('M d, Y') }}</div>
                    <div class="text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</div>
                </td>
            </tr>
        @endforeach
    </x-table>
</div>
@endsection
