@extends('components.auth')

@section('title', 'Email Verified')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] px-6">

    {{-- Success Icon + Message --}}
    <div class="text-center mb-8">
        <div class="flex items-center justify-center mb-4">
            <div class="bg-green-100 text-green-600 rounded-full p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <h1 class="text-3xl font-bold text-green-600 mb-2">Email Verified Successfully!</h1>
        <p class="text-gray-600 text-lg max-w-md mx-auto">
            Your account email address is verified. For your security, personal profile details and passwords are never included in email.
        </p>
    </div>

    {{-- Action Card --}}
    <div class="bg-white p-6 rounded-xl shadow-md max-w-sm w-full text-center">
        <a href="{{ route('dashboard') }}"
           class="block w-full bg-gray-900 hover:bg-black text-white font-semibold py-3 rounded-lg transition">
           Go to Dashboard
        </a>
    </div>

</div>
@endsection
