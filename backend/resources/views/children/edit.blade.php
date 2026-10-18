@extends('components.app')

@section('title', 'Edit Child')

@section('content')
@php
    $addressParts = array_map('trim', explode(',', $child->address ?? ''));
    $barangay = $addressParts[0] ?? '';
    $municipality = $addressParts[1] ?? '';
    $province = $addressParts[2] ?? '';
    $region = $addressParts[3] ?? '';
@endphp

<div class="max-w-4xl mx-auto my-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <div class="px-8 py-8 bg-gray-50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Edit Child Profile</h1>
                    <p class="text-sm text-gray-500 mt-1">Update the child's profile and family information.</p>
                </div>
            </div>
        </div>

        {{-- Validation errors keep the form (and the entered values) visible; the success banner shows on the profile page. --}}
        <x-validation-errors class="mx-8 md:mx-12 mt-6" />

        <div id="form-container" class="p-8 md:p-12 max-h-[calc(100vh-240px)] overflow-y-auto custom-scroll">
            <form id="editChildForm" action="{{ route('children.update', $child->id) }}" method="POST" enctype="multipart/form-data" class="space-y-10"
                  data-confirm="Are you sure you want to update this child profile?">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Child Profile</h2>
                    <p class="text-gray-500">Sociodemographic and personal information.</p>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" maxlength="255" id="childFirst" name="first_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('first_name', $child->first_name) }}" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" maxlength="255" id="childMiddle" name="middle_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('middle_name', $child->middle_name) }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" maxlength="255" id="childLast" name="last_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('last_name', $child->last_name) }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Sex <span class="text-red-500">*</span></label>
                            <select id="childSex" name="sex" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none appearance-none bg-white" required>
                                <option value="">Select Sex</option>
                                <option value="Male" {{ old('sex', $child->sex) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('sex', $child->sex) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" id="childDob" name="date_of_birth" max="{{ now()->toDateString() }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none" value="{{ old('date_of_birth', $child->date_of_birth) }}" required>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-sm font-semibold text-gray-700">Child Address</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Barangay</label>
                                <input type="text" maxlength="255" id="childBarangay" name="barangay" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('barangay', $barangay) }}">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Municipality/City</label>
                                <input type="text" maxlength="255" id="childMunicipality" name="municipality" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('municipality', $municipality) }}">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Province</label>
                                <input type="text" maxlength="255" id="childProvince" name="province" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('province', $province) }}">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Region</label>
                                <input type="text" maxlength="255" id="childRegion" name="region" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('region', $region) }}">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Child’s Handedness <span class="text-red-500">*</span></label>
                            <select id="childHandedness" name="handedness" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none appearance-none bg-white">
                                <option value="right" {{ old('handedness', $child->handedness) == 'right' ? 'selected' : '' }}>Right</option>
                                <option value="left" {{ old('handedness', $child->handedness) == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="both" {{ old('handedness', $child->handedness) == 'both' ? 'selected' : '' }}>Both (Ambidextrous)</option>
                                <option value="not_yet_established" {{ old('handedness', $child->handedness) == 'not_yet_established' ? 'selected' : '' }}>Not yet established</option>
                            </select>
                        </div>

                        @php($isStudying = (string) old('is_studying', $child->is_studying ? '1' : '0') === '1')
                        <div class="flex items-center space-x-6 pt-8">
                            <label class="text-sm font-semibold text-gray-700">Is the child presently studying?</label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" id="childStudyingYes" name="is_studying" value="1" class="w-5 h-5 border-gray-300 text-blue-600 focus:ring-blue-500 transition" {{ $isStudying ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-700">Yes</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" id="childStudyingNo" name="is_studying" value="0" class="w-5 h-5 border-gray-300 text-blue-600 focus:ring-blue-500 transition" {{ $isStudying ? '' : 'checked' }}>
                                <span class="text-sm font-medium text-gray-700">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="schoolNameField" class="space-y-2 {{ $isStudying ? '' : 'hidden' }}">
                        <label class="text-sm font-semibold text-gray-700">School / Learning Center / Day Care</label>
                        <input type="text" maxlength="255" id="childSchool" name="school_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('school_name', $child->school_name) }}">
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Father’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" maxlength="255" id="fatherName" name="fathers_name" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name" value="{{ old('fathers_name', $child->fathers_name) }}">
                            <input type="number" id="fatherAge" name="fathers_age" min="10" max="120" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age" value="{{ old('fathers_age', $child->fathers_age) }}">
                            <input type="text" maxlength="255" id="fatherOccupation" name="fathers_occupation" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation" value="{{ old('fathers_occupation', $child->fathers_occupation) }}">
                            <x-education-select name="fathers_education" id="fatherEducation"  class="w-full border-gray-200 rounded-xl p-3 border outline-none" :value="old('fathers_education', $child->fathers_education)" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Mother’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" maxlength="255" id="motherName" name="mothers_name" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name" value="{{ old('mothers_name', $child->mothers_name) }}">
                            <input type="number" id="motherAge" name="mothers_age" min="10" max="120" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age" value="{{ old('mothers_age', $child->mothers_age) }}">
                            <input type="text" maxlength="255" id="motherOccupation" name="mothers_occupation" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation" value="{{ old('mothers_occupation', $child->mothers_occupation) }}">
                            <x-education-select name="mothers_education" id="motherEducation"  class="w-full border-gray-200 rounded-xl p-3 border outline-none" :value="old('mothers_education', $child->mothers_education)" />
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6 border border-blue-100">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Number of Siblings</label>
                        <input type="number" id="childSiblings" name="number_of_siblings" min="0" max="30" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none" value="{{ old('number_of_siblings', $child->number_of_siblings) }}">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Birth Order</label>
                        <input type="number" id="childBirthOrder" name="birth_order" min="1" max="31" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none" value="{{ old('birth_order', $child->birth_order) }}">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-sm font-semibold text-gray-700">Update Child Photo Identification</label>
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                <img id="photoPreview" 
                                     src="{{ $child->photo_path ? route('children.photo', $child->id) : asset('images/sample-child.jpg') }}"
                                     class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="photoInput" name="photo" accept="image/*" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="mt-2 text-xs text-gray-400">JPG, PNG or GIF. Max size 2MB.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-8 flex justify-end space-x-4">
                    <a href="{{ route('children.index') }}" 
                       class="px-8 py-4 text-gray-600 font-bold hover:text-gray-900 transition">Cancel Changes</a>
                    <button type="submit" id="saveBtn"
                            class="px-12 py-4 bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-100 hover:bg-green-700 transition transform active:scale-95">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('cspNonce') }}">
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('photoPreview').src = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    const yesBox = document.getElementById('childStudyingYes');
    const noBox = document.getElementById('childStudyingNo');
    const schoolField = document.getElementById('schoolNameField');
    const syncSchoolField = () => schoolField.classList.toggle('hidden', !yesBox.checked);
    [yesBox, noBox].forEach((radio) => radio.addEventListener('change', syncSchoolField));
</script>
@endsection
