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

        <!-- Dynamic Success Message Container -->
        <div id="success-message" class="{{ session('success') ? '' : 'hidden' }} p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Profile Updated Successfully</h3>
                <p class="text-gray-600 mb-8">{{ session('success') ?? "The child's information has been updated and saved to the database." }}</p>
                <a href="{{ route('children.index') }}" 
                class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                    Back to Children
                </a>
            </div>
        </div>

        <!-- Dynamic Failed Message Container -->
        <div id="failed-message" class="{{ session('error') || $errors->any() ? '' : 'hidden' }} p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-red-100 text-red-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✕</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Update Failed</h3>
                <p class="text-gray-600 mb-8">
                    @if($errors->any())
                        {{ $errors->first() }}
                    @else
                        {{ session('error') ?? 'Something went wrong while updating the child profile. Please try again.' }}
                    @endif
                </p>
                <a href="{{ route('children.edit', $child->id) }}" 
                class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                    Try Again
                </a>
            </div>
        </div>

        <div id="form-container" class="{{ session('success') || session('error') || $errors->any() ? 'hidden' : '' }} p-8 md:p-12 max-h-[calc(100vh-240px)] overflow-y-auto custom-scroll">
            <form id="editChildForm" action="{{ route('children.update', $child->id) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
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
                            <input type="text" id="childFirst" name="first_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('first_name', $child->first_name) }}" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" id="childMiddle" name="middle_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('middle_name', $child->middle_name) }}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" id="childLast" name="last_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('last_name', $child->last_name) }}" required>
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
                            <input type="date" id="childDob" name="date_of_birth" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none" value="{{ old('date_of_birth', $child->date_of_birth) }}" required>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-sm font-semibold text-gray-700">Child Address</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Barangay</label>
                                <input type="text" id="childBarangay" name="barangay" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('barangay', $barangay) }}">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Municipality/City</label>
                                <input type="text" id="childMunicipality" name="municipality" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('municipality', $municipality) }}">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Province</label>
                                <input type="text" id="childProvince" name="province" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('province', $province) }}">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Region</label>
                                <input type="text" id="childRegion" name="region" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('region', $region) }}">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Child’s Handedness</label>
                            <select id="childHandedness" name="handedness" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none appearance-none bg-white">
                                <option value="">Select Handedness</option>
                                <option value="right" {{ old('handedness', $child->handedness) == 'right' ? 'selected' : '' }}>Right</option>
                                <option value="left" {{ old('handedness', $child->handedness) == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="both" {{ old('handedness', $child->handedness) == 'both' ? 'selected' : '' }}>Both (Ambidextrous)</option>
                                <option value="not_yet_established" {{ old('handedness', $child->handedness) == 'not_yet_established' ? 'selected' : '' }}>Not yet established</option>
                            </select>
                        </div>

                        <!-- Hidden input to submit boolean value for is_studying -->
                        <input type="hidden" name="is_studying" id="is_studying" value="{{ old('is_studying', $child->is_studying ? '1' : '0') }}">

                        <div class="flex items-center space-x-6 pt-8">
                            <label class="text-sm font-semibold text-gray-700">Is the child presently studying?</label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" id="childStudyingYes" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition" {{ old('is_studying', $child->is_studying) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-700">Yes</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" id="childStudyingNo" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition" {{ old('is_studying', $child->is_studying) ? '' : 'checked' }}>
                                <span class="text-sm font-medium text-gray-700">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="schoolNameField" class="space-y-2 {{ old('is_studying', $child->is_studying) ? '' : 'hidden' }}">
                        <label class="text-sm font-semibold text-gray-700">School / Learning Center / Day Care</label>
                        <input type="text" id="childSchool" name="school_name" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" value="{{ old('school_name', $child->school_name) }}">
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Father’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" id="fatherName" name="fathers_name" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name" value="{{ old('fathers_name', $child->fathers_name) }}">
                            <input type="number" id="fatherAge" name="fathers_age" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age" value="{{ old('fathers_age', $child->fathers_age) }}">
                            <input type="text" id="fatherOccupation" name="fathers_occupation" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation" value="{{ old('fathers_occupation', $child->fathers_occupation) }}">
                            <input type="text" id="fatherEducation" name="fathers_education" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Educational Attainment" value="{{ old('fathers_education', $child->fathers_education) }}">
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Mother’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" id="motherName" name="mothers_name" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name" value="{{ old('mothers_name', $child->mothers_name) }}">
                            <input type="number" id="motherAge" name="mothers_age" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age" value="{{ old('mothers_age', $child->mothers_age) }}">
                            <input type="text" id="motherOccupation" name="mothers_occupation" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation" value="{{ old('mothers_occupation', $child->mothers_occupation) }}">
                            <input type="text" id="motherEducation" name="mothers_education" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Educational Attainment" value="{{ old('mothers_education', $child->mothers_education) }}">
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6 border border-blue-100">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Number of Siblings</label>
                        <input type="number" id="childSiblings" name="number_of_siblings" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none" value="{{ old('number_of_siblings', $child->number_of_siblings) }}">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Birth Order</label>
                        <input type="number" id="childBirthOrder" name="birth_order" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none" value="{{ old('birth_order', $child->birth_order) }}">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-sm font-semibold text-gray-700">Update Child Photo Identification</label>
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                <img id="photoPreview" 
                                     src="{{ $child->photo_path ? asset('storage/' . $child->photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($child->first_name . ' ' . $child->last_name) . '&background=DBEAFE&color=1E40AF' }}" 
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
                    <button type="button" id="saveBtn"
                            class="px-12 py-4 bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-100 hover:bg-green-700 transition transform active:scale-95">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const saveBtn = document.getElementById('saveBtn');
    const editChildForm = document.getElementById('editChildForm');

    saveBtn.addEventListener('click', () => {
        const userConfirmed = confirm("Are you sure you want to update your profile?");
        if (!userConfirmed) {
            alert("Update action was cancelled.");
            return;
        }
        editChildForm.submit();
    });

    // Photo Preview
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

    // Studying Checkbox Toggle Logic
    const yesBox = document.getElementById('childStudyingYes');
    const noBox = document.getElementById('childStudyingNo');
    const schoolField = document.getElementById('schoolNameField');
    const schoolInput = document.getElementById('childSchool');
    const isStudyingInput = document.getElementById('is_studying');

    function toggleStudying(isStudying) {
        if (isStudying) {
            yesBox.checked = true;
            noBox.checked = false;
            schoolField.classList.remove('hidden');
            isStudyingInput.value = '1';
        } else {
            yesBox.checked = false;
            noBox.checked = true;
            schoolField.classList.add('hidden');
            schoolInput.value = ''; // Clears the school input field
            isStudyingInput.value = '0';
        }
    }

    yesBox.addEventListener('change', () => toggleStudying(yesBox.checked));
    noBox.addEventListener('change', () => toggleStudying(!noBox.checked));
</script>
@endsection