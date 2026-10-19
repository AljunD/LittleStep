@extends('settings.layout')

@section('title', 'Profile Settings')

@php
    $fullName = trim(preg_replace('/\s+/', ' ', $teacher->first_name.' '.$teacher->middle_name.' '.$teacher->last_name));
    $profileSections = [
        'Personal Information' => [
            'First Name' => $teacher->first_name,
            'Middle Name' => $teacher->middle_name,
            'Last Name' => $teacher->last_name,
            'Sex' => $teacher->sex,
        ],
        'Contact Details' => [
            'Email Address' => $user->email,
            'Contact Number' => $teacher->contact_number,
            'Address' => $teacher->address,
        ],
        'Account' => [
            'Role' => ucfirst($user->role),
            'Email Status' => $user->hasVerifiedEmail() ? 'Verified' : 'Not verified',
            'Member Since' => $user->created_at?->format('F d, Y'),
        ],
    ];
    $passwordFields = [
        ['name' => 'current_password', 'label' => 'Current Password', 'autocomplete' => 'current-password', 'minlength' => null, 'maxlength' => 1024],
        ['name' => 'password', 'label' => 'New Password', 'autocomplete' => 'new-password', 'minlength' => 12, 'maxlength' => 72],
        ['name' => 'password_confirmation', 'label' => 'Confirm New Password', 'autocomplete' => 'new-password', 'minlength' => 12, 'maxlength' => 72],
    ];
    $inputClass = 'mt-1 h-10 w-full rounded-xl border bg-gray-50/60 px-3 text-sm text-gray-800 transition focus:bg-white focus:outline-none focus:ring-4';
@endphp

@section('settings')
<div class="space-y-6">
    <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
        <div class="flex flex-col gap-4 border-b border-gray-100 p-6 sm:flex-row sm:items-center">
            <x-person :name="$fullName" :subtitle="$user->email" class="[&>span]:h-14 [&>span]:w-14 [&>span]:text-base" />
            <x-badge :color="$user->hasVerifiedEmail() ? 'green' : 'amber'" dot class="sm:ml-auto">
                {{ $user->hasVerifiedEmail() ? 'Email verified' : 'Email not verified' }}
            </x-badge>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($profileSections as $heading => $fields)
                <section class="p-6">
                    <h2 class="text-sm font-extrabold text-gray-900">{{ $heading }}</h2>
                    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        @foreach($fields as $label => $value)
                            <div @class(['sm:col-span-2' => $label === 'Address'])>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-800">
                                    @if(filled($value))
                                        {{ $value }}
                                    @else
                                        <span class="font-normal italic text-gray-400">Not provided</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>
    </div>

    <form id="change-password" method="POST" action="{{ route('settings.password.update') }}"
          class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
        @csrf
        @method('PUT')

        <div class="border-b border-gray-100 p-6">
            <h2 class="text-base font-extrabold text-gray-900">Change Password</h2>
            <p class="text-sm text-gray-500">Use 12 to 72 characters. After the change, other devices signed in to your account are signed out.</p>
        </div>

        <div class="space-y-4 p-6">
            <x-validation-errors class="mb-0" />

            <div class="grid grid-cols-1 gap-4 sm:max-w-md">
                @foreach($passwordFields as $field)
                    @php
                        $hasError = $errors->has($field['name']);
                    @endphp
                    <div>
                        <label for="{{ $field['name'] }}" class="text-sm font-semibold text-gray-700">{{ $field['label'] }}</label>
                        <input type="password" id="{{ $field['name'] }}" name="{{ $field['name'] }}" autocomplete="{{ $field['autocomplete'] }}"
                               @if($field['minlength']) minlength="{{ $field['minlength'] }}" @endif maxlength="{{ $field['maxlength'] }}" required
                               @if($hasError) aria-invalid="true" aria-describedby="{{ $field['name'] }}-error" @endif
                               class="{{ $inputClass }} {{ $hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-500/10' : 'border-gray-200 focus:border-blue-500 focus:ring-blue-500/10' }}">
                        @error($field['name'])
                            <p id="{{ $field['name'] }}-error" class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end border-t border-gray-100 bg-gray-50/60 px-6 py-4">
            <x-button type="submit" icon="save">Update Password</x-button>
        </div>
    </form>
</div>
@endsection
