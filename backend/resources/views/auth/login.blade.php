@extends('components.auth')

@section('title', 'Secure Login')

@section('content')
<div class="mb-10 text-center">
    <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tighter">Welcome Back!</h1>
    <p class="text-gray-400 text-lg font-light">Please sign in with your credentials</p>
</div>

<form method="POST" action="{{ route('auth.login.submit') }}" class="space-y-6" data-lock-submit>
    @csrf

    <div>
        <label for="email" class="block text-sm font-semibold text-gray-600 mb-2">Email Address</label>
        <input type="email" id="email" name="email"
               value="{{ old('email') }}"
               placeholder="username@gmail.com"
               maxlength="255" autocomplete="username" autocapitalize="none" spellcheck="false"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition" required autofocus>
        @error('email')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">Password</label>
        <div class="relative">
            <input type="password" id="password" name="password"
                   placeholder="••••••••"
                   maxlength="1024" autocomplete="current-password"
                   class="w-full pl-4 pr-12 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition" required>
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