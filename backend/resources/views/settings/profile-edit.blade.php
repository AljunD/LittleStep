@extends('settings.layout')

@section('title', 'Edit Profile')

@section('settings')
<form id="editTeacherProfileForm" method="POST" action="{{ route('settings.profile.update') }}"
      class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
    @csrf
    @method('PUT')

    <div class="border-b border-gray-100 p-6">
        <h2 class="text-base font-extrabold text-gray-900">Edit Profile</h2>
        <p class="text-sm text-gray-500">Update your personal and contact details.</p>
    </div>

    <div class="space-y-6 p-6">
        <x-validation-errors class="mb-0" />

        <section class="space-y-4">
            <h3 class="text-sm font-extrabold text-gray-900">Personal Information</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-settings-field name="first_name" label="First Name" :value="old('first_name', $teacher->first_name)"
                                  maxlength="255" autocomplete="given-name" required />
                <x-settings-field name="middle_name" label="Middle Name (optional)" :value="old('middle_name', $teacher->middle_name)"
                                  maxlength="255" autocomplete="additional-name" />
                <x-settings-field name="last_name" label="Last Name" :value="old('last_name', $teacher->last_name)"
                                  maxlength="255" autocomplete="family-name" required />
            </div>
            <div class="sm:max-w-xs">
                <x-settings-field name="sex" label="Sex" :value="old('sex', $teacher->sex)"
                                  :options="array_combine(\App\Support\FormRules::SEXES, \App\Support\FormRules::SEXES)" required />
            </div>
        </section>

        <section class="space-y-4 border-t border-gray-100 pt-6">
            <h3 class="text-sm font-extrabold text-gray-900">Contact Details</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="email" class="text-sm font-semibold text-gray-700">Email Address</label>
                    {{-- Shown for reference only: no name attribute, so it is never submitted. --}}
                    <input type="email" id="email" value="{{ $user->email }}" disabled aria-describedby="email-hint"
                           class="mt-1 h-10 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-3 text-sm text-gray-500">
                    <p id="email-hint" class="mt-1 text-xs text-gray-500">Your sign-in email can't be changed here.</p>
                </div>
                <x-settings-field type="tel" name="contact_number" label="Contact Number" :value="old('contact_number', $teacher->contact_number)"
                                  hint="11 digits starting with 09, e.g. 09171234567" maxlength="11" pattern="09[0-9]{9}"
                                  inputmode="numeric" data-digits-only autocomplete="tel" required />
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-settings-field name="barangay" label="Barangay" :value="old('barangay', $address['barangay'])" maxlength="255" required />
                <x-settings-field name="municipality" label="Municipality / City" :value="old('municipality', $address['municipality'])" maxlength="255" required />
                <x-settings-field name="province" label="Province" :value="old('province', $address['province'])" maxlength="255" required />
                <x-settings-field name="region" label="Region" :value="old('region', $address['region'])" maxlength="255" required />
            </div>
        </section>
    </div>

    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4">
        <x-button variant="secondary" :href="route('settings.profile')">Cancel</x-button>
        <x-button type="submit" icon="save">Save Changes</x-button>
    </div>
</form>
@endsection
