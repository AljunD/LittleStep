@extends('components.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-5">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Portal Dashboard</h1>
            <p class="text-sm text-gray-500">Overview of student progress tracking and school portal records.</p>
        </div>
    </div>

    <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-100 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Classroom Performance Metrics</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Registered Guardians</p>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-gray-900">{{ $guardiansCount }}</span>
                    <span class="text-[10px] font-medium text-gray-400">Guardian Accounts</span>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Linked Children</p>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-gray-900">{{ $childrenCount }}</span>
                    <span class="text-[10px] font-medium text-gray-400">Children Table Records</span>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Completed Progress Records</p>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-gray-900">{{ $completedRecordsCount }}</span>
                    <span class="text-[10px] font-medium text-gray-400">Finalized Evaluations</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Unfinished Progress Records</p>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span class="text-2xl font-black text-gray-900">{{ $unfinishedRecordsCount }}</span>
                    <span class="text-[10px] font-medium text-amber-600 font-bold">Pending or In Progress</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-6">
            <div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Evaluation Action Alerts</h3>
                <p class="text-xs text-gray-400 mt-0.5">Records demanding teacher action, grading, or observation logs.</p>
            </div>
            
            @if($urgentAlertRecord)
                <div class="bg-red-50/60 p-4 rounded-xl border border-red-100 space-y-3">
                    <div>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200">
                            Action Required
                        </span>
                        <h4 class="text-sm font-bold text-gray-900 mt-1.5">
                            {{ $urgentAlertRecord->child->first_name }} {{ $urgentAlertRecord->child->middle_name }} {{ $urgentAlertRecord->child->last_name }}
                        </h4>
                        <p class="text-[11px] text-red-700 mt-0.5 font-medium">
                            Evaluation checklist completely empty.
                        </p>
                    </div>
                    
                    <div class="pt-2 border-t border-red-200/40 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Record Status:</span>
                            <span class="font-bold text-red-700 uppercase text-[10px]">{{ $urgentAlertRecord->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Evaluation No:</span>
                            <span class="font-semibold text-gray-800">Test #{{ $urgentAlertRecord->evaluation_number }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 italic pt-1">Please initialize domain values to begin tracking.</p>
                    </div>
                </div>
            @else
                <div class="p-4 text-center border border-dashed border-gray-200 rounded-xl">
                    <p class="text-xs text-gray-400 font-medium">No urgent pending actions.</p>
                </div>
            @endif

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Incomplete Tasks Queue</h4>
                
                <div class="space-y-2.5">
                    @forelse($incompleteTasksQueue as $record)
                        <div class="p-3 bg-yellow-50/40 rounded-lg border border-yellow-100 space-y-1">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-yellow-900">
                                    {{ $record->child->first_name }} {{ Str::upper(Str::substr($record->child->middle_name, 0, 1)) }}. {{ $record->child->last_name }}
                                </span>
                                <span class="text-[10px] font-bold text-yellow-700 uppercase bg-yellow-50 border border-yellow-200 px-1.5 rounded">
                                    {{ str_replace('_', ' ', $record->status) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-600">
                                Missing items or written details inside the domain milestone tracking charts.
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-2">All tasks up to date.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm lg:col-span-2 space-y-4">
            <div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Teacher Activity History Log</h3>
                <p class="text-xs text-gray-400 mt-0.5">Live changes tracking from recent system logs.</p>
            </div>

            <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto pr-2">
                @forelse($recentLogs as $log)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                        <div class="space-y-0.5">
                            <div class="flex items-center space-x-2">
                                @php
                                    $badgeColors = match(Str::lower($log->action)) {
                                        'created', 'insert' => 'text-blue-700 bg-blue-50 border-blue-100',
                                        'updated', 'edit' => 'text-green-700 bg-green-50 border-green-100',
                                        default => 'text-purple-700 bg-purple-50 border-purple-100'
                                    };
                                @endphp
                                <span class="font-bold border px-1.5 py-0.5 rounded uppercase text-[9px] tracking-wide {{ $badgeColors }}">
                                    {{ $log->action }}
                                </span>
                                <span class="font-bold text-gray-800">{{ $log->entity_type }}</span>
                            </div>
                            <p class="text-gray-500">{{ $log->details ?? "Modified entry ID: $log->entity_id" }}</p>
                        </div>
                        <span class="text-gray-400 font-medium text-[11px] whitespace-nowrap">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-gray-400">No recent history items found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Master Student Evaluation Registry</h3>
                <p class="text-xs text-gray-400 mt-0.5">List of milestone trackers registered on the children table matrix.</p>
            </div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest bg-gray-50 border border-gray-100 px-2 py-1 rounded">
                progress_records logs
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Child Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Assigned Teacher</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Evaluation Date</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Evaluation No.</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status Enum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-xs">
                    @forelse($registryRecords as $record)
                        <tr class="hover:bg-blue-50/20 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                {{ $record->child->first_name }} {{ $record->child->middle_name }} {{ $record->child->last_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">
                                {{ $record->teacher->first_name }} {{ $record->teacher->last_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ \Carbon\Carbon::parse($record->evaluation_date)->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-700">
                                {{ $record->evaluation_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = match($record->status) {
                                        'completed' => 'bg-green-50 text-green-700 border-green-100',
                                        'in_progress' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                        default => 'bg-gray-50 text-gray-700 border-gray-100'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded font-bold border uppercase text-[10px] {{ $statusClasses }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">
                                No student assessment evaluations found in registry.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection