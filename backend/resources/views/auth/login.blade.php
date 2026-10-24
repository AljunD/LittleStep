@extends('components.auth')

@section('title', 'Secure Login')

@section('content')
<div class="mb-10 text-center">
    <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tighter">Welcome Back!</h1>
    <p class="text-gray-400 text-lg font-light">Please sign in with your credentials</p>
</div>

@php
    $loginAlerts = [
        'credentials' => [
            'tone' => 'error',
            'icon' => 'fa-circle-exclamation',
            'title' => 'Incorrect email or password',
            'message' => 'Check your details and try again. Passwords are case-sensitive.',
        ],
        'not_teacher' => [
            'tone' => 'error',
            'icon' => 'fa-user-lock',
            'title' => 'Teacher accounts only',
            'message' => $errors->first('email').' Guardians sign in with the LittleStep mobile app.',
        ],
        'unverified' => [
            'tone' => 'warning',
            'icon' => 'fa-envelope-open-text',
            'title' => 'Verify your email first',
            'message' => $errors->first('email'),
        ],
        'throttled' => [
            'tone' => 'warning',
            'icon' => 'fa-clock',
            'title' => 'Too many attempts',
            'message' => $errors->first('email').' This keeps your account safe.',
        ],
    ];
    $loginAlert = $loginAlerts[session('login_alert')] ?? null;
    $alertTones = [
        'error' => ['box' => 'border-red-200 border-l-red-500 bg-red-50', 'badge' => 'bg-red-100 text-red-600', 'title' => 'text-red-900', 'text' => 'text-red-700', 'close' => 'text-red-400 hover:text-red-700'],
        'warning' => ['box' => 'border-amber-200 border-l-amber-500 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-600', 'title' => 'text-amber-900', 'text' => 'text-amber-700', 'close' => 'text-amber-400 hover:text-amber-700'],
    ];
    $highlightFields = session('login_alert') === 'credentials';
    $inputBorder = $highlightFields
        ? 'border-red-400 ring-2 ring-red-100 focus:border-red-500 focus:ring-red-200'
        : 'border-gray-300 focus:border-gray-900 focus:ring-gray-200';
@endphp

@if ($loginAlert)
    @php($tone = $alertTones[$loginAlert['tone']])
    <div data-flash="login" role="alert" aria-live="assertive"
         class="mb-6 flex items-start gap-3 rounded-xl border border-l-4 p-4 shadow-sm {{ $tone['box'] }}">
        <div aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $tone['badge'] }}">
            <i class="fas {{ $loginAlert['icon'] }}"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-bold {{ $tone['title'] }}">{{ $loginAlert['title'] }}</p>
            <p class="mt-0.5 text-sm {{ $tone['text'] }}">{{ $loginAlert['message'] }}</p>
        </div>
        <button type="button" data-dismiss-alert aria-label="Dismiss message"
                class="px-1 text-lg font-bold leading-none {{ $tone['close'] }}">&times;</button>
    </div>
@endif

<form method="POST" action="{{ route('auth.login.submit') }}" class="space-y-6" data-lock-submit>
    @csrf

    <div>
        <label for="email" class="block text-sm font-semibold text-gray-600 mb-2">Email Address</label>
        <input type="email" id="email" name="email"
               value="{{ old('email') }}"
               placeholder="username@gmail.com"
               maxlength="255" autocomplete="username" autocapitalize="none" spellcheck="false"
               @if ($highlightFields) aria-invalid="true" @endif
               class="w-full px-4 py-3 rounded-xl border bg-gray-50 focus:ring-2 outline-none transition {{ $inputBorder }}" required @unless ($highlightFields) autofocus @endunless>
        @if (! $loginAlert)
            @error('email')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        @endif
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">Password</label>
        <div class="relative">
            <input type="password" id="password" name="password"
                   placeholder="••••••••"
                   maxlength="1024" autocomplete="current-password"
                   @if ($highlightFields) aria-invalid="true" @endif
                   class="w-full pl-4 pr-12 py-3 rounded-xl border bg-gray-50 focus:ring-2 outline-none transition {{ $inputBorder }}" required @if ($highlightFields) autofocus @endif>
            <button type="button" data-password-toggle="password"
                    aria-label="Show password" aria-controls="password" aria-pressed="false"
                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-gray-400 transition hover:text-gray-700 focus:outline-none focus-visible:text-gray-900 focus-visible:ring-2 focus-visible:ring-gray-300">
                <x-icon name="view" data-password-icon="show" />
                <x-icon name="view-off" class="hidden" data-password-icon="hide" />
            </button>
        </div>
        @error('password')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-gray-500 cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-gray-300 text-gray-900 focus:ring-0">
            <span>Remember me</span>
        </label>
        <a href="{{ route('auth.password.request') }}" 
           class="text-gray-500 hover:text-gray-900 underline underline-offset-2 transition">
           Forgot Password?
        </a>
    </div>

    <div class="pt-4">
        <x-button type="submit" class="w-full" data-submitting-text="Signing in...">Sign in</x-button>
    </div>
</form>
@endsection