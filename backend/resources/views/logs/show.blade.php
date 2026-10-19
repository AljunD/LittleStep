@extends('components.app')

@section('title', 'Log Entry')

@php
    use App\Support\AuditLog;

    $category = AuditLog::actionCategory($log->action);
@endphp

@section('content')
<div class="space-y-6">
    <x-page-header title="Log Entry #{{ $log->id }}" description="Recorded {{ $log->created_at->diffForHumans() }}.">
        <x-button variant="secondary" icon="back" :href="route('logs.index')">All Logs</x-button>
    </x-page-header>

    <div class="max-w-3xl overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
        <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 p-6">
            <x-log-action :action="$log->action" />
            @if($category)
                <span class="text-sm text-gray-500">{{ AuditLog::CATEGORIES[$category] }}</span>
            @endif
        </div>

        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-6 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Date and Time</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $log->created_at->format('F d, Y h:i:s A T') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Record</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-800">
                    @if($recordUrl)
                        <a href="{{ $recordUrl }}" class="text-blue-600 hover:text-blue-700 hover:underline">{{ AuditLog::entityLabel($log->entity_type) }}</a>
                    @else
                        {{ AuditLog::entityLabel($log->entity_type) }}
                    @endif
                    @if($log->entity_id)<span class="font-normal text-gray-400"> #{{ $log->entity_id }}</span>@endif
                    @if(! $recordUrl && $log->entity_id && in_array($log->entity_type, ['Child', 'Guardian', 'ProgressRecord', 'ChecklistItem'], true))
                        <span class="block text-xs font-normal text-gray-400">This record is archived or no longer available.</span>
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Details</dt>
                <dd class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $log->details ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">IP Address</dt>
                <dd class="mt-1 font-mono text-sm text-gray-800">{{ $log->ip_address ?? 'Not recorded' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Device</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-800">{{ AuditLog::describeDevice($log->user_agent) }}</dd>
            </div>
            @if($log->user_agent)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Browser Details</dt>
                    <dd class="mt-1 break-all font-mono text-xs text-gray-500">{{ $log->user_agent }}</dd>
                </div>
            @endif
        </dl>

        @if(in_array($log->action, ['login_failed', 'login_denied'], true))
            <div class="flex flex-col gap-3 border-t border-gray-100 bg-red-50/60 px-6 py-4 text-sm text-red-800 sm:flex-row sm:items-center sm:justify-between">
                <p>Don't recognize this sign-in attempt? Change your password to keep your account safe.</p>
                <x-button variant="danger" :href="route('settings.profile').'#change-password'">Change Password</x-button>
            </div>
        @endif
    </div>
</div>
@endsection
