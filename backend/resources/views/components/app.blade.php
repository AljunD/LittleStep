<!DOCTYPE html>
@php
  $displayTheme = \App\Support\DisplayTheme::current();
@endphp
<html lang="en" data-theme="{{ $displayTheme }}" @class(['dark' => $displayTheme === 'dark'])>
<head>
  @if ($displayTheme === 'system')
  <script nonce="{{ request()->attributes->get('cspNonce') }}">
    (() => {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
      const applyTheme = () => document.documentElement.classList.toggle('dark', prefersDark.matches);
      applyTheme();
      prefersDark.addEventListener('change', applyTheme);
    })();
  </script>
  @endif
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>@hasSection('title')@yield('title') · @endif LittleStep</title>
  <link rel="icon" type="image/png" href="{{ asset('images/littlestep-mark.png') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
</head>
<body class="h-screen overflow-hidden bg-[#f8f7f4] font-[Nunito] text-gray-800 antialiased">
  @php
    $teacherProfile = Auth::user()?->teacher;
    $displayName = trim(($teacherProfile->first_name ?? '').' '.($teacherProfile->last_name ?? '')) ?: (Auth::user()->email ?? '');
    $userInitials = strtoupper(substr($teacherProfile->first_name ?? Auth::user()->email ?? '', 0, 1).substr($teacherProfile->last_name ?? '', 0, 1));
  @endphp

  <div class="flex h-screen">
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-gray-200/70 bg-white
                  transition-transform duration-300 ease-in-out lg:static lg:w-64 lg:translate-x-0">

      <a href="{{ route('dashboard') }}" class="block px-6 pb-5 pt-6">
        {{-- The logo's navy wordmark needs a light badge on the dark theme. --}}
        <span class="inline-block dark:rounded-2xl dark:bg-[#ffffff] dark:px-3 dark:py-2">
          <img src="{{ asset('images/littlestep-logo.png') }}" alt="LittleStep" width="612" height="408" class="h-auto w-36">
        </span>
        <span class="mt-1 block text-xs font-bold uppercase tracking-widest text-gray-400">Teacher Portal</span>
      </a>

      @auth
        @if(Auth::user()->role === 'teacher')
        @php
          $navItems = [
            ['route' => 'dashboard',       'match' => 'dashboard',   'icon' => 'fa-house',          'chip' => 'bg-sky-100 text-sky-600',         'label' => 'Dashboard'],
            ['route' => 'guardians.index', 'match' => 'guardians.*', 'icon' => 'fa-people-roof',    'chip' => 'bg-violet-100 text-violet-600',   'label' => 'Guardians'],
            ['route' => 'children.index',  'match' => 'children.*',  'icon' => 'fa-child-reaching', 'chip' => 'bg-amber-100 text-amber-600',     'label' => 'Children'],
            ['route' => 'progress.index',  'match' => 'progress.*',  'icon' => 'fa-seedling',       'chip' => 'bg-emerald-100 text-emerald-600', 'label' => 'Progress'],
            ['route' => 'archives.index',  'match' => 'archives.*',  'icon' => 'fa-box-archive',    'chip' => 'bg-rose-100 text-rose-600',       'label' => 'Archives'],
            ['route' => 'logs.index',      'match' => 'logs.*',      'icon' => 'fa-clock-rotate-left', 'chip' => 'bg-slate-100 text-slate-600',  'label' => 'System Logs'],
            ['route' => 'settings.index',  'match' => 'settings.*',  'icon' => 'fa-gear',           'chip' => 'bg-gray-100 text-gray-600',       'label' => 'Settings'],
          ];
        @endphp
        <nav class="flex-1 space-y-1 overflow-y-auto px-4" aria-label="Main">
          <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">Menu</p>
          @foreach($navItems as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}"
               @if($active) aria-current="page" @endif
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition
                      {{ $active ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
              <span class="flex h-8 w-8 items-center justify-center rounded-lg text-sm {{ $item['chip'] }}" aria-hidden="true">
                <i class="fas {{ $item['icon'] }}"></i>
              </span>
              <span>{{ $item['label'] }}</span>
            </a>
          @endforeach
        </nav>
        @endif
      @endauth

      <div class="mt-auto p-4">
        @auth
        <div class="flex items-center gap-3 rounded-2xl bg-gray-50 p-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700" aria-hidden="true">
            {{ $userInitials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-gray-900">{{ $displayName }}</p>
            <p class="text-xs font-semibold text-gray-400">{{ ucfirst(Auth::user()->role) }}</p>
          </div>
          <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button type="submit" aria-label="Sign out" title="Sign out"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-red-50 hover:text-red-500">
              <i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>
            </button>
          </form>
        </div>
        @endauth
      </div>
    </aside>

    <div class="flex h-screen min-w-0 flex-1 flex-col">
      <div class="sticky top-0 z-40 flex items-center justify-between border-b border-gray-200/70 bg-white px-4 py-3 lg:hidden">
        <button type="button" id="sidebar-toggle" aria-label="Open navigation menu" class="-ml-1 rounded-xl p-2 text-gray-700 hover:bg-gray-100">
          <i class="fas fa-bars text-xl" aria-hidden="true"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
          <img src="{{ asset('images/littlestep-mark.png') }}" alt="" width="256" height="256" class="h-8 w-8 dark:rounded-lg dark:bg-[#ffffff]">
          <span class="text-base font-extrabold tracking-tight text-gray-900">LittleStep</span>
        </a>
        @auth
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700" aria-hidden="true">
          {{ $userInitials }}
        </div>
        @endauth
      </div>

      <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 md:px-8 lg:px-10 lg:py-10">
        <div class="mx-auto max-w-7xl">
          <x-flash-messages />
          @yield('content')
        </div>
      </main>
    </div>
  </div>

  <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden"></div>

  <script nonce="{{ request()->attributes->get('cspNonce') }}">
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebar-overlay');
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    }

    document.getElementById('sidebar-toggle')?.addEventListener('click', toggleSidebar);
    document.getElementById('sidebar-overlay')?.addEventListener('click', toggleSidebar);
    document.addEventListener('submit', event => {
      const form = event.target;
      if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
      }
      if (form.method.toLowerCase() !== 'post') return;
      if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
      }
      form.dataset.submitting = 'true';
      form.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = true; });
    });
    window.addEventListener('pageshow', event => {
      if (!event.persisted) return;
      document.querySelectorAll('form[data-submitting="true"]').forEach(form => {
        form.dataset.submitting = 'false';
        form.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = false; });
      });
    });
    document.addEventListener('click', event => {
      event.target.closest('[data-dismiss-alert]')?.closest('[data-flash]')?.remove();
    });
    document.addEventListener('change', event => {
      if (event.target.matches('select[data-auto-submit]')) event.target.form?.requestSubmit();
    });

    const menuItems = panel => [...panel.querySelectorAll('[role="menuitem"]:not([disabled])')];
    function placeActionMenu(trigger, panel) {
      const button = trigger.getBoundingClientRect();
      if (button.bottom < 0 || button.top > window.innerHeight) return closeActionMenus();
      const gap = 6, edge = 8;
      const left = Math.min(Math.max(button.right - panel.offsetWidth, edge), window.innerWidth - panel.offsetWidth - edge);
      const below = button.bottom + gap;
      const top = below + panel.offsetHeight > window.innerHeight - edge ? Math.max(button.top - panel.offsetHeight - gap, edge) : below;
      panel.style.left = `${left}px`;
      panel.style.top = `${top}px`;
    }
    function placeOpenActionMenu() {
      const trigger = document.querySelector('[data-action-menu-trigger][aria-expanded="true"]');
      if (trigger) placeActionMenu(trigger, trigger.parentElement.querySelector('[data-action-menu-panel]'));
    }
    function closeActionMenus(except = null) {
      document.querySelectorAll('[data-action-menu-trigger][aria-expanded="true"]').forEach(trigger => {
        if (trigger === except) return;
        trigger.setAttribute('aria-expanded', 'false');
        trigger.parentElement.querySelector('[data-action-menu-panel]').hidden = true;
      });
    }
    function openActionMenu(trigger) {
      const panel = trigger.parentElement.querySelector('[data-action-menu-panel]');
      closeActionMenus(trigger);
      trigger.setAttribute('aria-expanded', 'true');
      panel.hidden = false;
      placeActionMenu(trigger, panel);
      menuItems(panel)[0]?.focus({ preventScroll: true });
    }
    document.addEventListener('click', event => {
      const trigger = event.target.closest('[data-action-menu-trigger]');
      if (trigger) {
        trigger.getAttribute('aria-expanded') === 'true' ? closeActionMenus() : openActionMenu(trigger);
        return;
      }
      if (!event.target.closest('[data-action-menu-panel]')) closeActionMenus();
    });
    document.addEventListener('keydown', event => {
      const panel = event.target.closest('[data-action-menu-panel]');
      if (event.key === 'Escape' && panel) {
        closeActionMenus();
        panel.parentElement.querySelector('[data-action-menu-trigger]').focus();
      } else if (panel && ['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
        event.preventDefault();
        const items = menuItems(panel);
        const index = items.indexOf(document.activeElement);
        const next = { ArrowDown: index + 1, ArrowUp: index - 1, Home: 0, End: items.length - 1 }[event.key];
        items[(next + items.length) % items.length]?.focus();
      } else if (event.key === 'Tab' && panel) {
        closeActionMenus();
      }
    });
    document.addEventListener('scroll', placeOpenActionMenu, true);
    window.addEventListener('resize', placeOpenActionMenu);
  </script>
</body>
</html>
