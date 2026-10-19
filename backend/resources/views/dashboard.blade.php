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

        <section class="space-y-5 rounded-2xl border border-gray-200/70 bg-white p-6 lg:col-span-2" data-dashboard-insights>
            <div>
                <h2 class="text-base font-extrabold text-gray-900">Class Insights</h2>
                <p class="text-sm text-gray-500">
                    @if($insights['scored_children'] > 0)
                        Based on the latest scored evaluation of {{ $insights['scored_children'] }} {{ Str::plural('child', $insights['scored_children']) }}.
                    @else
                        Insights appear here once you save a child's first evaluation.
                    @endif
                </p>
            </div>

            @if($insights['scored_children'] === 0)
                <div class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-gray-200 py-10 text-center">
                    <i class="fas fa-chart-simple text-2xl text-gray-300" aria-hidden="true"></i>
                    <p class="text-sm text-gray-500">No scored evaluations yet.</p>
                </div>
            @else
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Overall Development</h3>
                    <div class="flex h-3 overflow-hidden rounded-full bg-gray-100" role="img"
                         aria-label="{{ collect($insights['overall'])->map(fn ($band) => $band['label'].': '.$band['count'])->implode(', ') }}">
                        @foreach($insights['overall'] as $band)
                            @if($band['count'] > 0)
                                <span class="{{ $band['bar'] }}" @style(['width: '.$band['percent'].'%'])></span>
                            @endif
                        @endforeach
                    </div>
                    <ul class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                        @foreach($insights['overall'] as $band)
                            <li class="rounded-xl bg-gray-50 px-3 py-2">
                                <p class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                    <span class="h-2 w-2 shrink-0 rounded-full {{ $band['bar'] }}" aria-hidden="true"></span>{{ $band['label'] }}
                                </p>
                                <p class="text-lg font-extrabold text-gray-900">{{ $band['count'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Domains to Focus On</h3>
                        <ul class="space-y-3">
                            @foreach($insights['domains'] as $domain)
                                <li>
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-semibold text-gray-800">{{ $domain['label'] }}</span>
                                        @if($domain['needs_support'] > 0)
                                            <x-badge color="amber">{{ $domain['needs_support'] }} of {{ $domain['scored'] }} need support</x-badge>
                                        @elseif($domain['scored'] > 0)
                                            <span class="text-xs text-gray-400">On track</span>
                                        @else
                                            <span class="text-xs text-gray-400">No scores</span>
                                        @endif
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100">
                                            <div @class(['h-full rounded-full', 'bg-amber-400' => $domain['needs_support'] > 0, 'bg-emerald-400' => $domain['needs_support'] === 0])
                                                 @style(['width: '.round(($domain['average'] ?? 0) / 19 * 100).'%'])></div>
                                        </div>
                                        <span class="w-16 text-right text-xs text-gray-500">avg {{ $domain['average'] ?? '—' }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <p class="text-xs text-gray-400">Average scaled score (1–19). A score of {{ \App\Services\EccdExpertSystem::DELAY_MAX_SCALED_SCORE }} or below suggests a delay.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Monitoring Schedule</h3>
                            @if($insights['follow_ups_total'] > $insights['follow_ups']->count())
                                <span class="text-xs text-gray-400">{{ $insights['follow_ups']->count() }} of {{ $insights['follow_ups_total'] }}</span>
                            @endif
                        </div>
                        <ul class="space-y-2">
                            @forelse($insights['follow_ups'] as $followUp)
                                <li>
                                    <a href="{{ route('progress.show', $followUp->record_id) }}"
                                       class="flex items-center justify-between gap-3 rounded-xl bg-gray-50 p-3 hover:bg-gray-100">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-gray-900">{{ $followUp->child_name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ \App\Services\DashboardInsightsService::OVERALL[$followUp->interpretation_code]['label'] }} · re-check in {{ $followUp->months }} months
                                            </p>
                                        </div>
                                        @if($followUp->due_on === null)
                                            <x-badge>No date</x-badge>
                                        @elseif($followUp->overdue)
                                            <x-badge color="red" dot>Overdue · {{ $followUp->due_on->format('M d, Y') }}</x-badge>
                                        @else
                                            <x-badge color="sky">Due {{ $followUp->due_on->format('M d, Y') }}</x-badge>
                                        @endif
                                    </a>
                                </li>
                            @empty
                                <li class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-gray-200 py-8 text-center">
                                    <i class="fas fa-face-smile text-2xl text-emerald-400" aria-hidden="true"></i>
                                    <p class="text-sm text-gray-500">No child currently needs a monitoring re-check.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endif
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
