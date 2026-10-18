<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>@yield('title', 'KidWatch')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>
<body class="bg-[#f4f7fe] font-[Inter] h-screen overflow-hidden">

  <div class="flex h-screen">
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-[#081028] shadow-2xl flex flex-col
                  -translate-x-full lg:translate-x-0 lg:static lg:w-64 lg:shadow-none
                  transition-transform duration-300 ease-in-out border-r border-white/5">

      <div class="px-8 py-10">
        <h1 class="text-xl font-black tracking-widest text-white uppercase">LITTLESTEP</h1>
      </div>

      @auth
        @if(Auth::user()->role === 'teacher')
        <nav class="flex-1 px-4 py-2 space-y-2 overflow-y-auto">
          <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-400 hover:bg-white/5 hover:text-white transition">
            <i class="fas fa-tachometer-alt text-blue-500"></i>
            <span>Dashboard</span>
          </a>
          <a href="{{ route('guardians.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-400 hover:bg-white/5 hover:text-white transition">
            <i class="fas fa-users text-blue-500"></i>
            <span>Guardians</span>
          </a>
          <a href="{{ route('children.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-400 hover:bg-white/5 hover:text-white transition">
              <i class="fas fa-user-graduate text-blue-500"></i>
              <span>Children</span>
          </a>
          <a href="{{ route('progress.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-400 hover:bg-white/5 hover:text-white transition">
            <i class="fas fa-chart-line text-blue-500"></i>
            <span>Progress</span>
          </a>
          <a href="{{ route('archives.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-400 hover:bg-white/5 hover:text-white transition">
            <i class="fas fa-box-archive text-blue-500"></i>
            <span>Archives</span>
          </a>
          <a href="{{ route('logs.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-400 hover:bg-white/5 hover:text-white transition">
            <i class="fas fa-clipboard-list text-blue-500"></i>
            <span>System Logs</span>
          </a>
        </nav>
        @endif
      @endauth

      <div class="p-6 border-t border-white/5 mt-auto">
        @auth
        <div class="flex items-center gap-3 bg-white/5 p-3 rounded-2xl border border-white/5">
          <div class="w-9 h-9 bg-white text-[#081028] rounded-full flex items-center justify-center font-bold shadow-md text-xs">
            {{ strtoupper(substr(Auth::user()->teacher->first_name ?? Auth::user()->email, 0, 2)) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-white text-sm truncate">
              {{ Auth::user()->teacher->first_name ?? '' }} {{ Auth::user()->teacher->last_name ?? '' }}
            </p>
            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-tighter">
              {{ ucfirst(Auth::user()->role) }}
            </p>
          </div>
          <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button type="submit" aria-label="Sign out" title="Sign out"
                    class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-red-400 transition-colors">
              <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            </button>
          </form>
        </div>
        @endauth
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 h-screen">
      <div class="lg:hidden bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between sticky top-0 z-40 shadow-sm">
        <button type="button" id="sidebar-toggle" aria-label="Open navigation menu" class="text-[#081028] p-2 -ml-2 rounded-xl hover:bg-slate-50">
          <i class="fas fa-bars text-2xl" aria-hidden="true"></i>
        </button>
        <h1 class="text-lg font-black text-[#081028] uppercase tracking-tighter">KidWatch</h1>
        @auth
        <div class="w-8 h-8 bg-[#081028] text-white rounded-full flex items-center justify-center font-bold text-[10px]">
          {{ strtoupper(substr(Auth::user()->teacher->first_name ?? Auth::user()->email, 0, 2)) }}
        </div>
        @endauth
      </div>

      <main class="flex-1 p-6 md:p-8 lg:p-10 overflow-y-auto">
        <x-flash-messages />
        @yield('content')
      </main>
    </div>
  </div>

  <div id="sidebar-overlay" class="hidden lg:hidden fixed inset-0 bg-black/60 z-40 backdrop-blur-sm transition-opacity duration-300"></div>

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
