@extends('components.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] px-6">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Forgot Password</h1>
        <p class="text-gray-600 text-lg max-w-md mx-auto">
            Enter your email to receive a reset link.
        </p>
    </div>

    {{-- Success / Error Alerts --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-700 px-6 py-4 rounded-lg mb-6 shadow max-w-md w-full text-center">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 px-6 py-4 rounded-lg mb-6 shadow max-w-md w-full text-center">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Reset Form --}}
    <div class="bg-white p-6 rounded-xl shadow-md max-w-sm w-full">
        <form method="POST" action="{{ route('auth.password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-600 mb-2">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="username@gmail.com" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition" required>
            </div>
            <button type="submit"
                class="w-full bg-gray-900 hover:bg-black text-white font-semibold py-3 rounded-lg transition shadow-md active:scale-95">
                Send Reset Link
            </button>
        </form>
    </div>

</div>
@endsection
