@extends('components.app')

@section('title', 'System Logs')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Logs</h1>
            <p class="text-sm text-gray-500">View all recorded actions across the system.</p>
        </div>
        <a href="{{ route('dashboard') }}" 
           class="px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow hover:bg-blue-700 transition transform active:scale-95">
            Back to Dashboard
        </a>
    </div>

    <form method="GET" class="flex flex-wrap gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
        <select name="user_id" class="border rounded px-3 py-2 text-sm">
            <option value="">All Users</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->email }}
                </option>
            @endforeach
        </select>

        <select name="action" class="border rounded px-3 py-2 text-sm">
            <option value="">All Actions</option>
            <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
            <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
            <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
            <option value="restored" {{ request('action') == 'restored' ? 'selected' : '' }}>Restored</option>
            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
            <option value="login_failed" {{ request('action') == 'login_failed' ? 'selected' : '' }}>Login Failed</option>
            <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
            <option value="viewed" {{ request('action') == 'viewed' ? 'selected' : '' }}>Viewed</option>
        </select>

        <input type="text" name="entity_type" placeholder="Entity Type" value="{{ request('entity_type') }}" class="border rounded px-3 py-2 text-sm">

        <input type="date" name="from_date" value="{{ request('from_date') }}" class="border rounded px-3 py-2 text-sm">
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="border rounded px-3 py-2 text-sm">

        <input type="text" name="search" placeholder="Search details..." value="{{ request('search') }}" class="border rounded px-3 py-2 text-sm flex-1">

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
            Filter
        </button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Entity</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Details</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-5 text-sm text-gray-900">
                                {{ $log->user->email ?? 'Unknown User' }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-700">
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-600">
                                {{ $log->entity_type }} #{{ $log->entity_id }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-500 max-w-xs truncate">
                                {{ $log->details }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-500">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-5 text-center text-gray-500">
                                No logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
