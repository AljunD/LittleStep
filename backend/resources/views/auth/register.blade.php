@extends('components.auth')

@section('title', 'Teacher Registration')

@section('content')
<div class="mb-10 text-center">
    <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tighter">Teacher Registration</h1>
    <p class="text-gray-400 text-lg font-light">Fill in your details to create an account</p>
</div>

<form method="POST" action="{{ route('auth.register.submit') }}" class="space-y-6">
    @csrf

    {{-- First Name --}}
    <div>
        <label for="first_name" class="block text-sm font-semibold text-gray-600 mb-2">First Name</label>
        <input type="text" id="first_name" name="first_name" placeholder="Juan"
               value="{{ old('first_name') }}"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
        @error('first_name')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Middle Name --}}
    <div>
        <label for="middle_name" class="block text-sm font-semibold text-gray-600 mb-2">Middle Name</label>
        <input type="text" id="middle_name" name="middle_name" placeholder="Santos"
               value="{{ old('middle_name') }}"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
        @error('middle_name')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Last Name --}}
    <div>
        <label for="last_name" class="block text-sm font-semibold text-gray-600 mb-2">Last Name</label>
        <input type="text" id="last_name" name="last_name" placeholder="Dela Cruz"
               value="{{ old('last_name') }}"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
        @error('last_name')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Sex --}}
    <div>
        <label for="sex" class="block text-sm font-semibold text-gray-600 mb-2">Sex</label>
        <select id="sex" name="sex"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
            <option value="" disabled selected>Select sex</option>
            <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
        </select>
        @error('sex')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>


    {{-- Contact Number --}}
    <div>
        <label for="contact_number" class="block text-sm font-semibold text-gray-600 mb-2">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" placeholder="09123456789"
               value="{{ old('contact_number') }}"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
        @error('contact_number')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Address --}}
    <div>
        <label for="address" class="block text-sm font-semibold text-gray-600 mb-2">Address</label>
        <textarea id="address" name="address" rows="3" placeholder="123 Barangay Street, Quezon City"
                  class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">{{ old('address') }}</textarea>
        @error('address')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="block text-sm font-semibold text-gray-600 mb-2">Email Address</label>
        <input type="email" id="email" name="email" placeholder="teacher@example.com"
               value="{{ old('email') }}"
               class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition">
        @error('email')
            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">Password</label>
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
        class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 rounded-xl transition shadow-md active:scale-95">
        Register
    </button>
</form>

<div class="text-center mt-6">
    <p class="text-sm text-gray-500">
        Already registered?
        <a href="{{ route('login') }}" class="font-medium text-gray-900 hover:text-black underline underline-offset-4">
            Sign in here
        </a>
    </p>
</div>
@endsection
