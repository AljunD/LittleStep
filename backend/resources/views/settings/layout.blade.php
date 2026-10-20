@extends('components.app')

{{-- Settings shell: tab list on the left, the selected tab's content (section "settings") on the right. Add new tabs to $settingsTabs. --}}
@php
    $settingsTabs = [
        ['route' => 'settings.profile', 'match' => 'settings.profile*', 'icon' => 'fa-user', 'label' => 'Profile', 'description' => 'Your personal and account details'],
        ['route' => 'settings.display', 'match' => 'settings.display*', 'icon' => 'fa-circle-half-stroke', 'label' => 'Display', 'description' => 'Light, dark, or system theme'],
        ['route' => 'settings.help', 'match' => 'settings.help', 'icon' => 'fa-circle-question', 'label' => 'Help & About', 'description' => 'Guides and information'],
    ];
@endphp

@section('content')
<div class="space-y-6">
    <x-page-header title="Settings" description="Manage your teacher account." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[16rem_1fr]">
        <nav class="h-fit space-y-1 rounded-2xl border border-gray-200/70 bg-white p-2" aria-label="Settings">
            @foreach($settingsTabs as $tab)
                @php
                    $active = request()->routeIs($tab['match']);
                @endphp
                <a href="{{ route($tab['route']) }}"
                   @if($active) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ $active ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $active ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500' }}" aria-hidden="true">
                        <i class="fas {{ $tab['icon'] }} text-sm"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-bold">{{ $tab['label'] }}</span>
                        <span class="block truncate text-xs {{ $active ? 'text-blue-600/70 dark:text-blue-300/80' : 'text-gray-400' }}">{{ $tab['description'] }}</span>
                    </span>
                </a>
            @endforeach
        </nav>

        {{-- On large screens the tab content scrolls inside the viewport, so the header and tab list stay in place. --}}
        <div data-settings-panel
             class="min-w-0 lg:max-h-[calc(100vh-13rem)] lg:overflow-y-auto lg:overscroll-contain lg:pr-2 lg:[scrollbar-width:thin]">
            @yield('settings')
        </div>
    </div>
</div>
@endsection
