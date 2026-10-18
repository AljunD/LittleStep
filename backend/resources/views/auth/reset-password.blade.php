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

    {{-- Status messages come from <x-flash-messages /> in the layout. --}}
    <x-validation-errors class="max-w-md w-full" />

    {{-- Reset Form --}}
    <div class="bg-white p-6 rounded-xl shadow-md max-w-sm w-full">
        <form method="POST" action="{{ route('auth.password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ old('email', request()->query('email')) }}">

            {{-- New Password --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">New Password</label>
                <input type="password" id="password" name="password" minlength="12" maxlength="72" autocomplete="new-password" placeholder="12-72 characters" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
                @error('password')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-600 mb-2">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="12" maxlength="72" autocomplete="new-password" placeholder="12-72 characters" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
            </div>

            {{-- Submit --}}
            <x-button type="submit" class="w-full">Reset Password</x-button>
        </form>
    </div>

</div>
@endsection
