@extends('components.app')

@section('title', 'System Logs')

@php
    use App\Services\ActivityLogService;
    use App\Support\AuditLog;

    $activeCategory = request('category');
    $filterQuery = request()->except(['page', 'category']);
    $retentionDays = config('littlestep.audit_log_retention_days');
    $dateRanges = [
        'Today' => [now()->toDateString(), now()->toDateString()],
        'Last 7 days' => [now()->subDays(6)->toDateString(), now()->toDateString()],
        'Last 30 days' => [now()->subDays(29)->toDateString(), now()->toDateString()],
    ];
    $stats = [
        ['label' => 'Sign-ins', 'value' => $summary['sign_ins'], 'icon' => 'fa-right-to-bracket', 'chip' => 'bg-sky-100 text-sky-600'],
        ['label' => 'Failed sign-ins', 'value' => $summary['failed_sign_ins'], 'icon' => 'fa-triangle-exclamation', 'chip' => $summary['failed_sign_ins'] > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500'],
        ['label' => 'Changes', 'value' => $summary['changes'], 'icon' => 'fa-pen-to-square', 'chip' => 'bg-emerald-100 text-emerald-600'],
        ['label' => 'Views & downloads', 'value' => $summary['access'], 'icon' => 'fa-eye', 'chip' => 'bg-violet-100 text-violet-600'],
    ];
    $previousSignIn = $summary['previous_sign_in'];
@endphp

@section('content')
<div class="space-y-6">
    <x-page-header title="System Logs"
                   description="An activity history of your account. Only you can see these entries, and they cannot be edited or deleted.">
        <x-button variant="secondary" icon="download" :href="route('logs.export', request()->except('page'))">Export CSV</x-button>
    </x-page-header>

    @if($summary['recent_failed_sign_ins'] > 0)
        <div class="flex flex-col gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 sm:flex-row sm:items-center sm:justify-between" role="alert">
            <div class="flex items-start gap-3 text-sm text-red-800">
                <i class="fas fa-shield-halved mt-0.5" aria-hidden="true"></i>
                <p>
                    <span class="font-bold">{{ $summary['recent_failed_sign_ins'] }} failed or blocked sign-in {{ $summary['recent_failed_sign_ins'] === 1 ? 'attempt' : 'attempts' }}</span>
                    on your account in the last {{ ActivityLogService::FAILED_SIGN_IN_ALERT_DAYS }} days. If this wasn't you, change your password.
                </p>
            </div>
            <x-button variant="danger" :href="route('settings.profile').'#change-password'">Change Password</x-button>
        </div>
    @endif

    <section aria-label="Activity in the last {{ ActivityLogService::SUMMARY_DAYS }} days" class="space-y-3">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach($stats as $stat)
                <div class="flex items-center gap-3 rounded-2xl border border-gray-200/70 bg-white p-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $stat['chip'] }}" aria-hidden="true">
                        <i class="fas {{ $stat['icon'] }}"></i>
                    </span>
                    <div>
                        <p class="text-xl font-extrabold text-gray-900">{{ $stat['value'] }}</p>
                        <p class="text-xs font-semibold text-gray-500">{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-500">
            Counts cover the last {{ ActivityLogService::SUMMARY_DAYS }} days.
            @if($previousSignIn)
                Previous sign-in: <span class="font-semibold text-gray-700">{{ $previousSignIn->created_at->format('M d, Y h:i A') }}</span>
                · {{ AuditLog::describeDevice($previousSignIn->user_agent) }}@if($previousSignIn->ip_address) · {{ $previousSignIn->ip_address }}@endif.
            @endif
            @if($retentionDays)
                Entries older than {{ $retentionDays }} days are removed automatically.
            @endif
        </p>
    </section>

    <nav class="flex flex-wrap gap-1 rounded-xl bg-gray-100 p-1" aria-label="Log categories">
        @foreach(['' => 'All activity'] + AuditLog::CATEGORIES as $category => $label)
            @php
                $isActive = (string) $activeCategory === (string) $category;
            @endphp
            <a href="{{ route('logs.index', $filterQuery + array_filter(['category' => $category])) }}"
               @if($isActive) aria-current="page" @endif
               class="inline-flex h-9 items-center rounded-lg px-4 text-sm font-bold transition {{ $isActive ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="space-y-2">
        <x-search-bar placeholder="Search log details" :keep="array_filter(['category' => $activeCategory])">
            <x-filter-field name="action" label="Action" placeholder="All actions" :options="$actionOptions->all()" />
            <x-filter-field name="entity_type" label="Record" placeholder="All records" :options="$entityOptions->all()" />
            <x-filter-field type="date" name="from_date" label="From" />
            <x-filter-field type="date" name="to_date" label="To" />
        </x-search-bar>
        <div class="flex flex-wrap items-center gap-2 px-1 text-xs">
            <span class="font-semibold text-gray-500">Quick range:</span>
            @foreach($dateRanges as $label => [$from, $to])
                <a href="{{ route('logs.index', array_merge(request()->except(['page', 'from_date', 'to_date']), ['from_date' => $from, 'to_date' => $to])) }}"
                   class="rounded-full border px-3 py-1 font-semibold transition {{ request('from_date') === $from && request('to_date') === $to ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <x-table :columns="['When', 'Action', 'Record', 'Details', 'Device']" :paginator="$logs"
             :empty="request()->anyFilled(['search', 'category', 'action', 'entity_type', 'from_date', 'to_date']) ? 'No log entries match your filters.' : 'No activity recorded yet.'">
        @foreach($logs as $log)
            @php
                $recordUrl = $recordLinks["{$log->entity_type}:{$log->entity_id}"] ?? null;
            @endphp
            <tr>
                <td class="whitespace-nowrap" title="{{ $log->created_at->diffForHumans() }}">
                    <div class="font-semibold text-gray-800">{{ $log->created_at->format('M d, Y') }}</div>
                    <div class="text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</div>
                </td>
                <td class="whitespace-nowrap"><x-log-action :action="$log->action" /></td>
                <td class="whitespace-nowrap">
                    @if($recordUrl)
                        <a href="{{ $recordUrl }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">{{ AuditLog::entityLabel($log->entity_type) }}</a>
                    @else
                        <span class="font-semibold text-gray-800">{{ AuditLog::entityLabel($log->entity_type) }}</span>
                    @endif
                    @if($log->entity_id)<span class="text-gray-400"> #{{ $log->entity_id }}</span>@endif
                </td>
                <td class="max-w-md truncate" title="{{ $log->details }}">{{ $log->details ?? '—' }}</td>
                <td class="whitespace-nowrap">
                    <div class="text-gray-800">{{ AuditLog::describeDevice($log->user_agent) }}</div>
                    <div class="text-xs text-gray-400">{{ $log->ip_address ?? 'IP not recorded' }}</div>
                </td>
                <td class="whitespace-nowrap text-right">
                    <x-action-menu label="Actions for log entry {{ $log->id }}">
                        <x-action-menu.item :href="route('logs.show', $log->id)" icon="view">View Details</x-action-menu.item>
                        @if($recordUrl)
                            <x-action-menu.item :href="$recordUrl" icon="link">Open Record</x-action-menu.item>
                        @endif
                    </x-action-menu>
                </td>
            </tr>
        @endforeach
    </x-table>
</div>
@endsection
