@extends('components.auth')

@section('title', 'Reset Password')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] px-6">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Reset Your Password</h1>
        <p class="text-gray-600 text-lg max-w-md mx-auto">
            Enter a new password to secure your account.
        </p>
    </div>

    {{-- Success / Error Alerts --}}
    @if (session('status'))
        <div class="bg-green-100 text-green-700 px-6 py-4 rounded-lg mb-6 shadow max-w-md w-full text-center">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 px-6 py-4 rounded-lg mb-6 shadow max-w-md w-full text-center">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Reset Form --}}
    <div class="bg-white p-6 rounded-xl shadow-md max-w-sm w-full">
        <form method="POST" action="{{ route('auth.password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ old('email', request()->email) }}">

            {{-- New Password --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">New Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
                @error('password')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-600 mb-2">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full bg-gray-900 hover:bg-black text-white font-semibold py-3 rounded-lg transition shadow-md active:scale-95">
                Reset Password
            </button>
        </form>
    </div>

</div>
@endsection
