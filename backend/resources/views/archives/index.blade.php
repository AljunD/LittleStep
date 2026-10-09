@extends('components.app')

@section('title', 'Archives')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'guardians' }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Archives</h1>
            <p class="text-sm text-gray-500">View and restore archived guardians, user accounts, and child records.</p>
        </div>

        <div class="flex space-x-2 bg-gray-100 p-1.5 rounded-xl self-start">
            <button @click="activeTab = 'guardians'" 
                    :class="{ 'bg-white text-gray-900 shadow-sm': activeTab === 'guardians', 'text-gray-500 hover:text-gray-700': activeTab !== 'guardians' }"
                    class="px-5 py-2.5 text-sm font-bold rounded-lg transition-all">
                Archived Guardians ({{ $archivedGuardians->total() }})
            </button>
            <button @click="activeTab = 'children'" 
                    :class="{ 'bg-white text-gray-900 shadow-sm': activeTab === 'children', 'text-gray-500 hover:text-gray-700': activeTab !== 'children' }"
                    class="px-5 py-2.5 text-sm font-bold rounded-lg transition-all">
                Archived Children ({{ $archivedChildren->total() }})
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-8 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="max-w-md mx-auto">
                <div class="mb-4 bg-green-100 text-green-700 w-12 h-12 rounded-full flex items-center justify-center mx-auto text-xl font-bold">
                    ✓
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Restore Complete</h3>
                <p class="text-gray-600 mb-6 text-sm">
                    {{ session('success') }}
                </p>
                <a href="{{ route('archives.index') }}" 
                   class="inline-block px-6 py-2.5 bg-gray-900 text-white font-semibold text-sm rounded-xl hover:bg-gray-800 transition">
                    Return to Archives
                </a>
            </div>
        </div>
    @elseif(session('error'))
        <div class="p-8 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="max-w-md mx-auto">
                <div class="mb-4 bg-red-100 text-red-700 w-12 h-12 rounded-full flex items-center justify-center mx-auto text-xl font-bold">
                    ✕
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Restore Failed</h3>
                <p class="text-gray-600 mb-6 text-sm">
                    {{ session('error') }}
                </p>
                <a href="{{ route('archives.index') }}" 
                   class="inline-block px-6 py-2.5 bg-gray-900 text-white font-semibold text-sm rounded-xl hover:bg-gray-800 transition">
                    Back to Archives
                </a>
            </div>
        </div>
    @else

        <div x-show="activeTab === 'guardians'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                                    {{ $guardian->deleted_at ? $guardian->deleted_at->format('M d, Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('guardians.show', $guardian->id) }}" 
                                           class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                            View
                                        </a>
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

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
                {{ $archivedGuardians->links() }}
            </div>
        </div>

        <div x-show="activeTab === 'children'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-cloak>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Child Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Guardian</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Sex</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Archived At</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($archivedChildren as $child)
                            <tr class="hover:bg-red-50 transition-colors group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $child->first_name }} {{ $child->middle_name }} {{ $child->last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-500">
                                    @if($child->guardian)
                                        {{ $child->guardian->first_name }} {{ $child->guardian->last_name }}
                                    @else
                                        <span class="italic text-gray-400">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-600">
                                    {{ $child->sex }}
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-600">
                                    {{ $child->deleted_at ? $child->deleted_at->format('M d, Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right">
                                    <div class="flex justify-end space-x-2">
                                        <form action="{{ route('children.restore', $child->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to restore this child record?')">
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
                                <td colspan="5" class="px-6 py-5 text-center text-gray-500">
                                    No archived children found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
                {{ $archivedChildren->links() }}
            </div>
        </div>

    @endif
</div>
@endsection