@extends('components.auth')

@section('title', 'Secure Login')

@section('content')
<div class="mb-10 text-center">
    <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tighter">Welcome Back</h1>
    <p class="text-gray-400 text-lg font-light">Please sign in with your credentials</p>
</div>

{{-- Status Message (e.g. after password reset) --}}
@if (session('status'))
    <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-xl">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('auth.login.submit') }}" class="space-y-6" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerText = 'Signing in...'; btn.classList.add('opacity-75', 'cursor-not-allowed');">
    @csrf

    {{-- Email --}}
    <div>
        <label for="email" class="block text-sm font-semibold text-gray-600 mb-2">Email Address</label>
        <input type="email" id="email" name="email"
               value="{{ old('email') }}"
               placeholder="username@gmail.com"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition" required autofocus>
        @error('email')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">Password</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition" required>
        @error('password')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Remember + Forgot --}}
    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-gray-500 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-gray-900 focus:ring-0">
            <span>Remember me</span>
        </label>
        <a href="{{ route('auth.password.request') }}" 
           class="text-gray-500 hover:text-gray-900 underline underline-offset-2 transition">
           Forgot Password?
        </a>
    </div>

    {{-- Submit --}}
    <div class="pt-4">
        <button type="submit"
            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 rounded-xl transition shadow-md active:scale-95 disabled:opacity-75 disabled:cursor-not-allowed">
            Sign in
        </button>
    </div>
</form>
@endsection