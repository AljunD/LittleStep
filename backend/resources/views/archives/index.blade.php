@extends('components.app')

@section('title', 'Archives')

@section('content')
@php
    $activeTabClass = 'bg-white text-gray-900 shadow-sm';
    $inactiveTabClass = 'text-gray-500 hover:text-gray-800';
    $emptySuffix = request('search') ? ' match your search.' : ' yet.';
@endphp
<div class="space-y-6">
    <x-page-header title="Archives" description="View and restore archived guardians, user accounts, and child records.">
        <div class="flex gap-1 rounded-xl bg-gray-100 p-1" role="tablist" aria-label="Archive lists">
            <button type="button" role="tab" id="archive-tab-guardians" data-archive-tab="guardians"
                    aria-controls="archive-panel-guardians" aria-selected="{{ $activeTab === 'guardians' ? 'true' : 'false' }}"
                    class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-bold transition {{ $activeTab === 'guardians' ? $activeTabClass : $inactiveTabClass }}">
                Guardians <span class="text-xs font-semibold text-gray-400">{{ $archivedGuardians->total() }}</span>
            </button>
            <button type="button" role="tab" id="archive-tab-children" data-archive-tab="children"
                    aria-controls="archive-panel-children" aria-selected="{{ $activeTab === 'children' ? 'true' : 'false' }}"
                    class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-bold transition {{ $activeTab === 'children' ? $activeTabClass : $inactiveTabClass }}">
                Children <span class="text-xs font-semibold text-gray-400">{{ $archivedChildren->total() }}</span>
            </button>
        </div>
    </x-page-header>

    <x-search-bar placeholder="Search archived guardians and children by name" :keep="['tab' => $activeTab]" />

    <div id="archive-panel-guardians" role="tabpanel" aria-labelledby="archive-tab-guardians" data-archive-panel="guardians"
         @if ($activeTab !== 'guardians') hidden @endif>
        <x-table :columns="['Guardian', 'Archived On']" :paginator="$archivedGuardians" :empty="'No archived guardians'.$emptySuffix">
            @foreach($archivedGuardians as $guardian)
                <tr>
                    <td class="whitespace-nowrap">
                        <x-person :name="$guardian->first_name.' '.$guardian->last_name" :subtitle="$guardian->user->email ?? 'No email available'" />
                    </td>
                    <td class="whitespace-nowrap">{{ $guardian->deleted_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                    <td class="whitespace-nowrap text-right">
                        <x-action-menu label="Actions for {{ $guardian->first_name }} {{ $guardian->last_name }}">
                            <x-action-menu.item :href="route('guardians.show', $guardian->id)" icon="view">View</x-action-menu.item>
                            <form action="{{ route('guardians.restore', $guardian->id) }}" method="POST" data-confirm="Are you sure you want to restore this guardian and linked user account?">
                                @csrf
                                @method('PATCH')
                                <x-action-menu.item icon="restore" variant="success">Restore</x-action-menu.item>
                            </form>
                        </x-action-menu>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>

    <div id="archive-panel-children" role="tabpanel" aria-labelledby="archive-tab-children" data-archive-panel="children"
         @if ($activeTab !== 'children') hidden @endif>
        <x-table :columns="['Child', 'Sex', 'Archived On']" :paginator="$archivedChildren" :empty="'No archived children'.$emptySuffix">
            @foreach($archivedChildren as $child)
                <tr>
                    <td class="whitespace-nowrap">
                        <x-person :name="$child->first_name.' '.$child->middle_name.' '.$child->last_name"
                                  :subtitle="$child->guardian ? 'Guardian: '.$child->guardian->first_name.' '.$child->guardian->last_name : 'Unassigned'" />
                    </td>
                    <td class="whitespace-nowrap">{{ $child->sex }}</td>
                    <td class="whitespace-nowrap">{{ $child->deleted_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                    <td class="whitespace-nowrap text-right">
                        <x-action-menu label="Actions for {{ $child->first_name }} {{ $child->last_name }}">
                            <form action="{{ route('children.restore', $child->id) }}" method="POST" data-confirm="Are you sure you want to restore this child record?">
                                @csrf
                                @method('PATCH')
                                <x-action-menu.item icon="restore" variant="success">Restore</x-action-menu.item>
                            </form>
                        </x-action-menu>
                    </td>
                </tr>
            @endforeach
        </x-table>
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
        document.querySelectorAll('form[role="search"] input[name="tab"]').forEach((input) => {
            input.value = name;
        });

        const url = new URL(window.location.href);
        url.searchParams.set('tab', name);
        history.replaceState(null, '', url);
    };

    tabs.forEach((tab) => tab.addEventListener('click', () => showTab(tab.dataset.archiveTab)));
});
</script>
@endsection
