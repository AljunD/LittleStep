@extends('components.auth')

@section('title', 'Teacher Registration')

@php
    $fieldClass = 'w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:border-gray-900 focus:ring-2 focus:ring-gray-200 outline-none transition';
    $labelClass = 'block text-sm font-semibold text-gray-600 mb-2';
    $sectionClass = 'text-xs font-bold uppercase tracking-widest text-gray-400';
    $addressFields = [
        'barangay' => ['label' => 'Barangay', 'placeholder' => 'Balite'],
        'municipality' => ['label' => 'Municipality / City', 'placeholder' => 'Rodriguez'],
        'province' => ['label' => 'Province', 'placeholder' => 'Rizal'],
        'region' => ['label' => 'Region', 'placeholder' => 'CALABARZON'],
    ];
@endphp

@section('content')
<div class="mb-10 text-center">
    <h1 class="text-4xl font-bold text-gray-900 mb-2 tracking-tighter">Teacher Registration</h1>
    <p class="text-gray-400 text-lg font-light">Fill in your details to create an account</p>
</div>

@if (! $registrationOpen)
<div role="status" class="p-4 border rounded-xl bg-gray-50 border-gray-200 text-sm text-gray-600">
    Teacher registration is closed. Ask the day care center for a registration code.
</div>
@else
<form method="POST" action="{{ route('auth.register.submit') }}" class="space-y-8" data-lock-submit>
    @csrf

    @if ($codeRequired)
        <section class="space-y-4">
            <h2 class="{{ $sectionClass }}">Registration Code</h2>
            <div>
                <label for="registration_code" class="{{ $labelClass }}">Code from the day care center</label>
                <input type="text" id="registration_code" name="registration_code" value="{{ old('registration_code') }}"
                       maxlength="255" autocomplete="off" autocapitalize="none" spellcheck="false" required class="{{ $fieldClass }}">
                @error('registration_code') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </section>
    @endif

    <section class="space-y-4">
        <h2 class="{{ $sectionClass }}">Personal Information</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="first_name" class="{{ $labelClass }}">First Name</label>
                <input type="text" id="first_name" name="first_name" placeholder="Juan" value="{{ old('first_name') }}"
                       maxlength="255" autocomplete="given-name" required class="{{ $fieldClass }}">
                @error('first_name') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="middle_name" class="{{ $labelClass }}">Middle Name <span class="font-normal text-gray-400">(optional)</span></label>
                <input type="text" id="middle_name" name="middle_name" placeholder="Santos" value="{{ old('middle_name') }}"
                       maxlength="255" autocomplete="additional-name" class="{{ $fieldClass }}">
                @error('middle_name') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="last_name" class="{{ $labelClass }}">Last Name</label>
                <input type="text" id="last_name" name="last_name" placeholder="Dela Cruz" value="{{ old('last_name') }}"
                       maxlength="255" autocomplete="family-name" required class="{{ $fieldClass }}">
                @error('last_name') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="sex" class="{{ $labelClass }}">Sex</label>
                <select id="sex" name="sex" required class="{{ $fieldClass }}">
                    <option value="" disabled @selected(! old('sex'))>Select sex</option>
                    @foreach (\App\Support\FormRules::SEXES as $sex)
                        <option value="{{ $sex }}" @selected(old('sex') === $sex)>{{ $sex }}</option>
                    @endforeach
                </select>
                @error('sex') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <h2 class="{{ $sectionClass }}">Contact and Address</h2>
        <div>
            <label for="contact_number" class="{{ $labelClass }}">Contact Number</label>
            <input type="tel" id="contact_number" name="contact_number" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" data-digits-only
                   title="11 digits starting with 09, e.g. 09171234567" placeholder="09171234567" value="{{ old('contact_number') }}"
                   autocomplete="tel" required class="{{ $fieldClass }}">
            @error('contact_number') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($addressFields as $name => $field)
                <div>
                    <label for="{{ $name }}" class="{{ $labelClass }}">{{ $field['label'] }}</label>
                    <input type="text" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $field['placeholder'] }}" value="{{ old($name) }}"
                           maxlength="255" required class="{{ $fieldClass }}">
                    @error($name) <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </section>

    <section class="space-y-4">
        <h2 class="{{ $sectionClass }}">Account</h2>
        <div>
            <label for="email" class="{{ $labelClass }}">Email Address</label>
            <input type="email" id="email" name="email" placeholder="teacher@example.com" value="{{ old('email') }}"
                   maxlength="255" autocomplete="email" autocapitalize="none" spellcheck="false" required class="{{ $fieldClass }}">
            @error('email') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="{{ $labelClass }}">Password</label>
                <input type="password" id="password" name="password" minlength="12" maxlength="72" autocomplete="new-password"
                       placeholder="12-72 characters" required class="{{ $fieldClass }}">
                @error('password') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="{{ $labelClass }}">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="12" maxlength="72"
                       autocomplete="new-password" placeholder="12-72 characters" required class="{{ $fieldClass }}">
            </div>
        </div>
    </section>

    <x-button type="submit" class="w-full" data-submitting-text="Registering...">Register</x-button>
</form>
@endif

<div class="text-center mt-6">
    <p class="text-sm text-gray-500">
        Already registered?
        <a href="{{ route('login') }}" class="font-medium text-gray-900 hover:text-black underline underline-offset-4">
            Sign in here
        </a>
    </p>
</div>
@endsection
