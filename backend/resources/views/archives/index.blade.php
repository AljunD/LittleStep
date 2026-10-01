@extends('components.app')

@section('title', 'Archived Guardians')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Archived Guardians</h1>
            <p class="text-sm text-gray-500">View all guardians and user accounts that have been archived.</p>
        </div>
    </div>

    @if(session('success'))
        <!-- Restore Success Message -->
        <div class="p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
                    ✓
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Restore Complete</h3>
                <p class="text-gray-600 mb-8">
                    {{ session('success') }}
                </p>
                <a href="{{ route('archives.index') }}" 
                   class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                    Return to Archives
                </a>
            </div>
        </div>
    @elseif(session('error'))
        <!-- Restore Failed Message -->
        <div class="p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-red-100 text-red-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
                    ✕
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Restore Failed</h3>
                <p class="text-gray-600 mb-8">
                    {{ session('error') }}
                </p>
                <a href="{{ route('archives.index') }}" 
                   class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                    Back to Archives
                </a>
            </div>
        </div>
    @else
        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Guardian</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Archived At</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($archivedGuardians as $guardian)
                            <tr class="hover:bg-red-50 transition-colors group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $guardian->first_name }} {{ $guardian->last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-500">
                                    {{ $guardian->user->email ?? 'No email available' }}
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-600">
                                    {{ $guardian->deleted_at ? $guardian->deleted_at->format('M d, Y H:i') : '' }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right">
                                    <div class="flex justify-end space-x-2">
                                        <!-- View Button -->
                                        <a href="{{ route('guardians.show', $guardian->id) }}" 
                                           class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                            View
                                        </a>
                                        <!-- Restore Button -->
                                        <form action="{{ route('guardians.restore', $guardian->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to restore this guardian and linked user account?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-semibold hover:bg-green-200 transition">
                                                Restore
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-5 text-center text-gray-500">
                                    No archived guardians found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
                {{ $archivedGuardians->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
