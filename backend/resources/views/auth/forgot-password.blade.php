@extends('components.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] px-6">

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Forgot Password</h1>
        <p class="text-gray-600 text-lg max-w-md mx-auto">
            Enter your email to receive a reset link.
        </p>
    </div>

    <x-validation-errors class="max-w-md w-full" />

    <div class="bg-white p-6 rounded-xl shadow-md max-w-sm w-full">
        <form method="POST" action="{{ route('auth.password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-600 mb-2">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="username@gmail.com" maxlength="255" autocomplete="email" autocapitalize="none" spellcheck="false" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition" required>
            </div>
            <x-button type="submit" class="w-full">Send Reset Link</x-button>
        </form>
    </div>

</div>
@endsection
