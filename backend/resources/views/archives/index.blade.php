@extends('components.app')

@section('title', 'Archives')

@section('content')
@php
    $activeTabClass = 'bg-white text-gray-900 shadow-sm';
    $inactiveTabClass = 'text-gray-500 hover:text-gray-700';
@endphp
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Archives</h1>
            <p class="text-sm text-gray-500">View and restore archived guardians, user accounts, and child records.</p>
        </div>

        <div class="flex space-x-2 bg-gray-100 p-1.5 rounded-xl self-start" role="tablist" aria-label="Archive lists">
            <button type="button" role="tab" id="archive-tab-guardians" data-archive-tab="guardians"
                    aria-controls="archive-panel-guardians" aria-selected="{{ $activeTab === 'guardians' ? 'true' : 'false' }}"
                    class="px-5 py-2.5 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'guardians' ? $activeTabClass : $inactiveTabClass }}">
                Archived Guardians ({{ $archivedGuardians->total() }})
            </button>
            <button type="button" role="tab" id="archive-tab-children" data-archive-tab="children"
                    aria-controls="archive-panel-children" aria-selected="{{ $activeTab === 'children' ? 'true' : 'false' }}"
                    class="px-5 py-2.5 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'children' ? $activeTabClass : $inactiveTabClass }}">
                Archived Children ({{ $archivedChildren->total() }})
            </button>
        </div>
    </div>

    {{-- Restore results are shown by the shared flash-messages banner in the layout, above the updated lists. --}}

        <div id="archive-panel-guardians" role="tabpanel" aria-labelledby="archive-tab-guardians" data-archive-panel="guardians"
             @if ($activeTab !== 'guardians') hidden @endif
             class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                                        <form action="{{ route('guardians.restore', $guardian->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to restore this guardian and linked user account?">
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

        <div id="archive-panel-children" role="tabpanel" aria-labelledby="archive-tab-children" data-archive-panel="children"
             @if ($activeTab !== 'children') hidden @endif
             class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                                        <form action="{{ route('children.restore', $child->id) }}" method="POST" class="inline-block" data-confirm="Are you sure you want to restore this child record?">
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
</div>

<script nonce="{{ request()->attributes->get('cspNonce') }}">
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('[data-archive-tab]');
    const activeClasses = @json(explode(' ', $activeTabClass));
    const inactiveClasses = @json(explode(' ', $inactiveTabClass));

    const showTab = (name) => {
        tabs.forEach((tab) => {
            const selected = tab.dataset.archiveTab === name;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.classList.remove(...(selected ? inactiveClasses : activeClasses));
            tab.classList.add(...(selected ? activeClasses : inactiveClasses));
        });
        document.querySelectorAll('[data-archive-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.archivePanel !== name;
        });

        // Keep the open tab on reload and when navigating back.
        const url = new URL(window.location.href);
        url.searchParams.set('tab', name);
        history.replaceState(null, '', url);
    };

    tabs.forEach((tab) => tab.addEventListener('click', () => showTab(tab.dataset.archiveTab)));
});
</script>
@endsection