@extends('components.app')

@section('title', 'Edit Guardian')

@section('content')
<div class="max-w-4xl mx-auto my-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <div id="form-header" class="px-8 py-8 bg-gray-50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Edit Guardian</h1>
                    <p class="text-sm text-gray-500 mt-1">Update guardian account and linked information</p>
                </div>
            </div>
        </div>

        {{-- Validation errors keep the form (and the entered values) visible; the success banner shows on the guardians list. --}}
        <x-validation-errors class="mx-8 md:mx-12 mt-6" />

        <div id="form-container" class="p-8 md:p-12">
            <form id="editGuardianForm" method="POST" action="{{ route('guardians.update', $guardian->id) }}" class="space-y-10"
                  data-confirm="Are you sure you want to save these changes?">
                @csrf
                @method('PUT')
                
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4">User Account</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Email Address</label>
                            <input type="email" id="guardianEmail" name="email" 
                                value="{{ old('email', $guardian->user->email ?? '') }}" 
                                placeholder="example@email.com" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('email') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Role</label>
                            <input type="text" maxlength="255" value="guardian" readonly 
                                class="w-full border-gray-100 bg-gray-50 text-gray-500 rounded-xl p-3 cursor-not-allowed border">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">New Password <span class="text-gray-400 text-xs">(optional)</span></label>
                            <input type="password" id="guardianPassword" name="password" minlength="12" maxlength="72" autocomplete="new-password"
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" 
                                placeholder="Leave blank to keep current password">
                            @error('password') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Confirm New Password</label>
                            <input type="password" id="guardianConfirmPassword" name="password_confirmation" minlength="12" maxlength="72" autocomplete="new-password"
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('password_confirmation') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Guardian Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">First Name</label>
                            <input type="text" maxlength="255" id="guardianFirst" name="first_name" value="{{ old('first_name', $guardian->first_name ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('first_name') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" maxlength="255" id="guardianMiddle" name="middle_name" value="{{ old('middle_name', $guardian->middle_name ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('middle_name') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Last Name</label>
                            <input type="text" maxlength="255" id="guardianLast" name="last_name" value="{{ old('last_name', $guardian->last_name ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('last_name') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Sex</label>
                            <select id="guardianSex" name="sex"
                                    class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border bg-white">
                                <option value="" disabled>Select sex</option>
                                <option value="Male" {{ old('sex', $guardian->sex ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('sex', $guardian->sex ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('sex') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Contact Number</label>
                            <input type="tel" id="guardianContact" name="contact_number" maxlength="11" pattern="09[0-9]{9}" inputmode="numeric" title="11 digits starting with 09, e.g. 09171234567" value="{{ old('contact_number', $guardian->contact_number ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('contact_number') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Relationship to Child</label>
                            <input type="text" maxlength="255" id="guardianRelation" name="relationship_to_child" value="{{ old('relationship_to_child', $guardian->relationship_to_child ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('relationship_to_child') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Guardian Address</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Barangay</label>
                            <input type="text" maxlength="255" id="guardianBarangay" name="barangay" value="{{ old('barangay', $guardian->barangay ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('barangay') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Municipality/City</label>
                            <input type="text" maxlength="255" id="guardianMunicipality" name="municipality" value="{{ old('municipality', $guardian->municipality ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('municipality') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Province</label>
                            <input type="text" maxlength="255" id="guardianProvince" name="province" value="{{ old('province', $guardian->province ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('province') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Region</label>
                            <input type="text" maxlength="255" id="guardianRegion" name="region" value="{{ old('region', $guardian->region ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            @error('region') <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3 pt-8 border-t border-gray-100">
                    <x-button variant="secondary" :href="route('guardians.index')">Cancel</x-button>
                    <x-button type="submit" icon="save" id="save-btn">Save Changes</x-button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
