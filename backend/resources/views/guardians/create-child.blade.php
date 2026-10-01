@extends('components.app')

@section('title', 'Add Child')

@section('content')
<div class="max-w-4xl mx-auto my-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Fixed Header -->
        <div class="px-8 py-8 bg-gray-50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Add New Child for {{ $guardian->first_name }} {{ $guardian->last_name }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Complete the child profile and family information.</p>
                </div>
            </div>
        </div>

        <!-- Validation Errors Alert -->
        @if ($errors->any())
            <div class="p-6 bg-red-50 border-b border-red-100">
                <p class="text-red-700 font-bold mb-2">Please correct the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Scrollable Form Area -->
        <div id="form-container" class="p-8 md:p-12 max-h-[calc(100vh-240px)] overflow-y-auto custom-scroll">
            <form id="createChildForm" 
                  action="{{ route('guardians.store-child', $guardian->id) }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="space-y-10">
                @csrf

                <!-- Child Profile Header -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Child Profile</h2>
                    <p class="text-gray-500 mt-1">Sociodemographic and family background.</p>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" id="childFirst" value="{{ old('first_name') }}" required
                                   class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" name="middle_name" id="childMiddle" value="{{ old('middle_name') }}" 
                                   class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" id="childLast" value="{{ old('last_name') }}" required
                                   class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Sex <span class="text-red-500">*</span></label>
                            <select name="sex" id="childSex" required
                                    class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none appearance-none bg-white">
                                <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" name="date_of_birth" id="childDob" value="{{ old('date_of_birth') }}" required
                                   class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                    </div>

                    <!-- Child Address -->
                    <div class="space-y-4">
                        <label class="text-sm font-semibold text-gray-700">Child Address</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Barangay</label>
                                <input type="text" name="barangay" id="childBarangay" value="{{ old('barangay') }}" 
                                       class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Barangay">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Municipality/City</label>
                                <input type="text" name="municipality" id="childMunicipality" value="{{ old('municipality') }}" 
                                       class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Municipality/City">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Province</label>
                                <input type="text" name="province" id="childProvince" value="{{ old('province') }}" 
                                       class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Province">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Region</label>
                                <input type="text" name="region" id="childRegion" value="{{ old('region') }}" 
                                       class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Region">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Child’s Handedness <span class="text-red-500">*</span></label>
                            <select name="handedness" id="childHandedness" required
                                    class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none appearance-none bg-white">
                                <option value="right" {{ old('handedness') == 'right' ? 'selected' : '' }}>Right</option>
                                <option value="left" {{ old('handedness') == 'left' ? 'selected' : '' }}>Left</option>
                                <option value="both" {{ old('handedness') == 'both' ? 'selected' : '' }}>Both</option>
                                <option value="not_yet_established" {{ old('handedness', 'not_yet_established') == 'not_yet_established' ? 'selected' : '' }}>Not yet established</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-6 pt-8">
                            <label class="text-sm font-semibold text-gray-700">Is the child presently studying?</label>
                            <input type="hidden" name="is_studying" id="isStudyingInput" value="{{ old('is_studying', 0) }}">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" id="childStudyingYes" {{ old('is_studying') == 1 ? 'checked' : '' }} 
                                       class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition">
                                <span class="text-sm font-medium text-gray-700">Yes</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" id="childStudyingNo" {{ old('is_studying', 0) == 0 ? 'checked' : '' }} 
                                       class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition">
                                <span class="text-sm font-medium text-gray-700">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="schoolNameField" class="space-y-2 {{ old('is_studying') == 1 ? '' : 'hidden' }}">
                        <label class="text-sm font-semibold text-gray-700">School / Learning Center / Day Care</label>
                        <input type="text" name="school_name" id="childSchool" value="{{ old('school_name') }}" 
                               class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="School name">
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Family Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Father’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" name="fathers_name" id="fatherName" value="{{ old('fathers_name') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name">
                            <input type="number" name="fathers_age" id="fatherAge" value="{{ old('fathers_age') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age">
                            <input type="text" name="fathers_occupation" id="fatherOccupation" value="{{ old('fathers_occupation') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation">

                            <!-- Drop-down for Educational Attainment -->
                            <select name="fathers_education" id="fatherEducation" class="w-full border-gray-200 rounded-xl p-3 border outline-none">
                                <option value="">Select Educational Attainment</option>
                                <option value="Elementary" {{ old('fathers_education') == 'Elementary' ? 'selected' : '' }}>Elementary</option>
                                <option value="High School" {{ old('fathers_education') == 'High School' ? 'selected' : '' }}>High School</option>
                                <option value="College" {{ old('fathers_education') == 'College' ? 'selected' : '' }}>College</option>
                                <option value="Vocational" {{ old('fathers_education') == 'Vocational' ? 'selected' : '' }}>Vocational</option>
                                <option value="Postgraduate" {{ old('fathers_education') == 'Postgraduate' ? 'selected' : '' }}>Postgraduate</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Mother’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" name="mothers_name" id="motherName" value="{{ old('mothers_name') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name">
                            <input type="number" name="mothers_age" id="motherAge" value="{{ old('mothers_age') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age">
                            <input type="text" name="mothers_occupation" id="motherOccupation" value="{{ old('mothers_occupation') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation">

                            <!-- Drop-down for Educational Attainment -->
                            <select name="mothers_education" id="motherEducation" class="w-full border-gray-200 rounded-xl p-3 border outline-none">
                                <option value="">Select Educational Attainment</option>
                                <option value="Elementary" {{ old('mothers_education') == 'Elementary' ? 'selected' : '' }}>Elementary</option>
                                <option value="High School" {{ old('mothers_education') == 'High School' ? 'selected' : '' }}>High School</option>
                                <option value="College" {{ old('mothers_education') == 'College' ? 'selected' : '' }}>College</option>
                                <option value="Vocational" {{ old('mothers_education') == 'Vocational' ? 'selected' : '' }}>Vocational</option>
                                <option value="Postgraduate" {{ old('mothers_education') == 'Postgraduate' ? 'selected' : '' }}>Postgraduate</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6 border border-blue-100">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Number of Siblings</label>
                        <input type="number" name="number_of_siblings" id="siblings" value="{{ old('number_of_siblings') }}" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Birth Order</label>
                        <input type="number" name="birth_order" id="birthOrder" value="{{ old('birth_order') }}" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="space-y-4">
                    <label class="text-sm font-semibold text-gray-700">Child Photo Identification</label>
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                <img id="photoPreview" src="" class="hidden w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="photo" id="photoInput" accept="image/*" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="mt-2 text-xs text-gray-400">JPG, PNG or GIF. Max size 2MB.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-8 flex justify-end space-x-4">
                    <a href="{{ route('guardians.index') }}" 
                       class="px-8 py-4 text-gray-600 font-bold hover:text-gray-900 transition">Cancel</a>
                    <button type="submit" id="saveBtn"
                            class="px-12 py-4 bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-100 hover:bg-green-700 transition transform active:scale-95">
                        Save Child
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Photo Preview
    document.getElementById('photoInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('photoPreview').src = ev.target.result;
                document.getElementById('photoPreview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Studying Checkbox Logic
    const yesBox = document.getElementById('childStudyingYes');
    const noBox = document.getElementById('childStudyingNo');
    const schoolField = document.getElementById('schoolNameField');
    const isStudyingInput = document.getElementById('isStudyingInput');

    yesBox?.addEventListener('change', () => {
        if (yesBox.checked) {
            noBox.checked = false;
            isStudyingInput.value = '1';
            schoolField.classList.remove('hidden');
        } else {
            isStudyingInput.value = '0';
            schoolField.classList.add('hidden');
        }
    });

    noBox?.addEventListener('change', () => {
        if (noBox.checked) {
            yesBox.checked = false;
            isStudyingInput.value = '0';
            schoolField.classList.add('hidden');
        }
    });
</script>
@endsection