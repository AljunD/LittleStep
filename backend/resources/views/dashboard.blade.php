@extends('components.app')

@section('title', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $teacherName = Auth::user()->teacher->first_name ?? '';
    $childName = fn ($child) => trim($child->first_name.' '.$child->middle_name.' '.$child->last_name);
    $stats = [
        ['label' => 'Guardians', 'value' => $guardiansCount, 'hint' => 'Linked to your children', 'icon' => 'fa-people-roof', 'chip' => 'bg-violet-100 text-violet-600'],
        ['label' => 'Children', 'value' => $childrenCount, 'hint' => 'Assigned to you', 'icon' => 'fa-child-reaching', 'chip' => 'bg-amber-100 text-amber-600'],
        ['label' => 'Completed Records', 'value' => $completedRecordsCount, 'hint' => 'Finalized evaluations', 'icon' => 'fa-circle-check', 'chip' => 'bg-emerald-100 text-emerald-600'],
        ['label' => 'Unfinished Records', 'value' => $unfinishedRecordsCount, 'hint' => 'Pending or in progress', 'icon' => 'fa-hourglass-half', 'chip' => 'bg-sky-100 text-sky-600'],
    ];
@endphp
<div class="space-y-6">
    <x-page-header :title="trim($greeting.', '.$teacherName, ', ').'!'" description="Here's an overview of your children and their progress records." >
        <x-button :href="route('progress.index')" icon="evaluate">Go to Progress</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <div class="flex items-center gap-4 rounded-2xl border border-gray-200/70 bg-white p-5">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-lg {{ $stat['chip'] }}" aria-hidden="true">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-500">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-400">{{ $stat['hint'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="space-y-4 rounded-2xl border border-gray-200/70 bg-white p-6">
            <div>
                <h2 class="text-base font-extrabold text-gray-900">Needs Attention</h2>
                <p class="text-sm text-gray-500">Records waiting for an evaluation or observation.</p>
            </div>

            @if($urgentAlertRecord)
                <div class="rounded-xl bg-rose-50 p-4">
                    <x-badge color="red">Action required</x-badge>
                    <p class="mt-2 font-bold text-gray-900">{{ $childName($urgentAlertRecord->child) }}</p>
                    <p class="text-sm text-gray-600">This record is pending your evaluation.</p>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                        <span>Evaluation no. {{ $urgentAlertRecord->evaluation_number }}</span>
                        <x-status-badge :status="$urgentAlertRecord->status" />
                    </div>
                </div>
            @endif

            <div class="space-y-2">
                @foreach($incompleteTasksQueue as $record)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-amber-50/70 p-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-900">{{ $childName($record->child) }}</p>
                            <p class="text-xs text-gray-500">Some domains or notes are still missing.</p>
                        </div>
                        <x-status-badge :status="$record->status" />
                    </div>
                @endforeach
            </div>

            @if(! $urgentAlertRecord && $incompleteTasksQueue->isEmpty())
                <div class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-gray-200 py-8 text-center">
                    <i class="fas fa-face-smile text-2xl text-emerald-400" aria-hidden="true"></i>
                    <p class="text-sm text-gray-500">All caught up. Nothing needs your attention.</p>
                </div>
            @endif
        </section>

        <section class="space-y-4 rounded-2xl border border-gray-200/70 bg-white p-6 lg:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold text-gray-900">Recent Activity</h2>
                    <p class="text-sm text-gray-500">Recent changes made from your account.</p>
                </div>
                <a href="{{ route('logs.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700">View all</a>
            </div>

            <ul class="divide-y divide-gray-100">
                @forelse($recentLogs as $log)
                    <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-log-action :action="$log->action" />
                            <p class="truncate text-sm text-gray-600">{{ $log->details ?? $log->entity_type.' #'.$log->entity_id }}</p>
                        </div>
                        <span class="whitespace-nowrap text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="py-8 text-center text-sm text-gray-400">No recent activity yet.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <section class="space-y-3">
        <div>
            <h2 class="text-base font-extrabold text-gray-900">Recent Evaluations</h2>
            <p class="text-sm text-gray-500">The latest progress records assigned to you.</p>
        </div>

        <x-table :columns="['Child', 'Teacher', 'Evaluation Date', 'Evaluation No.', 'Status']" :actions="false" empty="No evaluations recorded yet.">
            @foreach($registryRecords as $record)
                <tr>
                    <td class="whitespace-nowrap"><x-person :name="$childName($record->child)" /></td>
                    <td class="whitespace-nowrap">{{ $record->teacher->first_name }} {{ $record->teacher->last_name }}</td>
                    <td class="whitespace-nowrap">{{ $record->evaluation_date ? \Carbon\Carbon::parse($record->evaluation_date)->format('M d, Y') : '—' }}</td>
                    <td class="whitespace-nowrap font-semibold text-gray-800">{{ $record->evaluation_number }}</td>
                    <td class="whitespace-nowrap"><x-status-badge :status="$record->status" /></td>
                </tr>
            @endforeach
        </x-table>
    </section>
</div>
@endsection
