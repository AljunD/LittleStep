@extends('components.app')

@section('title', 'Add Guardian')

@section('content')
<div class="max-w-4xl mx-auto my-10 px-4 sm:px-6">
    <div class="mb-6">
        <a href="{{ route('guardians.index') }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Guardians
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="px-6 py-6 md:px-8 md:py-8 bg-gray-50 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900">Registration Portal</h2>
                    <p class="text-xs md:text-sm text-gray-500 mt-0.5">Register guardian credentials and link primary child details.</p>
                </div>
                
                <div class="flex items-center space-x-2 md:space-x-3">
                    <span id="label-step1" class="transition-all duration-300 text-[10px] md:text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-blue-600 text-white shadow-sm">Step 1</span>
                    <div class="h-px w-4 bg-gray-300"></div>
                    <span id="label-step2" class="transition-all duration-300 text-[10px] md:text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-gray-200 text-gray-500">Step 2</span>
                    <div class="h-px w-4 bg-gray-300"></div>
                    <span id="label-step3" class="transition-all duration-300 text-[10px] md:text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-gray-200 text-gray-500">Step 3</span>
                </div>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out" style="width: 33%;"></div>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-6 mx-6 md:mx-8 mt-6 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center space-x-2 text-red-800 font-bold mb-2">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Please correct the errors in the form before submitting:</span>
                </div>
                <ul class="list-disc list-inside text-xs md:text-sm text-red-600 space-y-1 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="success-message" class="{{ session('registration_success') ? '' : 'hidden' }} p-8 md:p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Registration Complete</h3>
                <p class="text-gray-600 mb-8 text-sm md:text-base">The guardian account, guardian profile, and child profile have been successfully created.</p>
                <a href="{{ route('guardians.index') }}" class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95 shadow-md">
                    Done
                </a>
            </div>
        </div>
        <form id="guardianRegistrationForm" action="{{ route('guardians.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div id="form-container" class="{{ session('registration_success') ? 'hidden' : '' }} p-6 md:p-12 max-h-[calc(100vh-220px)] overflow-y-auto custom-scroll">
                <div id="step1" class="space-y-8">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900">Guardian Account & Information</h2>
                        <p class="text-sm text-gray-500 mt-1">Login credentials and personal details of the guardian.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label for="guardianFirst" class="text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" id="guardianFirst" name="first_name" value="{{ old('first_name') }}" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="e.g. Maria">
                            @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="guardianMiddle" class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" id="guardianMiddle" name="middle_name" value="{{ old('middle_name') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="e.g. Lopez">
                            @error('middle_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="guardianLast" class="text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" id="guardianLast" name="last_name" value="{{ old('last_name') }}" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="e.g. Santos">
                            @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="guardianSex" class="text-sm font-semibold text-gray-700">Sex <span class="text-red-500">*</span></label>
                        <select id="guardianSex" name="sex" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm bg-white">
                            <option value="" disabled {{ old('sex') ? '' : 'selected' }}>Select sex</option>
                            <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('sex') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="guardianContact" class="text-sm font-semibold text-gray-700">Contact Number <span class="text-red-500">*</span></label>
                            <input type="tel" id="guardianContact" name="contact_number" value="{{ old('contact_number') }}" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="09171234567">
                            @error('contact_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="guardianRelation" class="text-sm font-semibold text-gray-700">Relationship to Child <span class="text-red-500">*</span></label>
                            <input type="text" id="guardianRelation" name="relationship_to_child" value="{{ old('relationship_to_child') }}" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Mother, Father, Legal Guardian, etc.">
                            @error('relationship_to_child') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-sm font-semibold text-gray-700 block">Guardian Address</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="guardianBarangay" class="text-xs text-gray-600 font-medium">Barangay</label>
                                <input type="text" id="guardianBarangay" name="barangay" value="{{ old('barangay') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Barangay">
                            </div>
                            <div class="space-y-2">
                                <label for="guardianMunicipality" class="text-xs text-gray-600 font-medium">Municipality/City</label>
                                <input type="text" id="guardianMunicipality" name="municipality" value="{{ old('municipality') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Municipality/City">
                            </div>
                            <div class="space-y-2">
                                <label for="guardianProvince" class="text-xs text-gray-600 font-medium">Province</label>
                                <input type="text" id="guardianProvince" name="province" value="{{ old('province') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Province">
                            </div>
                            <div class="space-y-2">
                                <label for="guardianRegion" class="text-xs text-gray-600 font-medium">Region</label>
                                <input type="text" id="guardianRegion" name="region" value="{{ old('region') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Region">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="guardianEmail" class="text-sm font-semibold text-gray-700">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" id="guardianEmail" name="email" value="{{ old('email') }}" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="name@example.com">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="guardianPassword" class="text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                            <input type="password" id="guardianPassword" name="password" value="{{ old('password', 'Password123!') }}" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="guardianConfirmPassword" class="text-sm font-semibold text-gray-700">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" id="guardianConfirmPassword" name="password_confirmation" value="{{ old('password_confirmation', 'Password123!') }}" required class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Account Role</label>
                        <input type="text" class="w-full border-gray-100 rounded-xl p-3 bg-gray-50 text-gray-500 cursor-not-allowed border text-sm" value="guardian" readonly>
                    </div>

                    <div class="pt-6 flex justify-end">
                        <button type="button" id="next1" class="px-10 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95 text-sm">
                            Next Step
                        </button>
                    </div>
                </div>

                <div id="step2" class="space-y-8 hidden">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900">Child Profile</h2>
                        <p class="text-sm text-gray-500 mt-1">Sociodemographic and family background.</p>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label for="childFirst" class="text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <input type="text" id="childFirst" name="child_first_name" value="{{ old('child_first_name') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border outline-none text-sm transition">
                                @error('child_first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label for="childMiddle" class="text-sm font-semibold text-gray-700">Middle Name</label>
                                <input type="text" id="childMiddle" name="child_middle_name" value="{{ old('child_middle_name') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border outline-none text-sm transition">
                            </div>
                            <div class="space-y-2">
                                <label for="childLast" class="text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" id="childLast" name="child_last_name" value="{{ old('child_last_name') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border outline-none text-sm transition">
                                @error('child_last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="childSex" class="text-sm font-semibold text-gray-700">Sex <span class="text-red-500">*</span></label>
                                <select id="childSex" name="child_sex" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border outline-none text-sm bg-white transition">
                                    <option value="" disabled {{ old('child_sex') ? '' : 'selected' }}>Select sex</option>
                                    <option value="Male" {{ old('child_sex') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('child_sex') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('child_sex') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label for="childDob" class="text-sm font-semibold text-gray-700">Date of Birth <span class="text-red-500">*</span></label>
                                <input type="date" id="childDob" name="child_date_of_birth" value="{{ old('child_date_of_birth') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 border outline-none text-sm transition">
                                @error('child_date_of_birth') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="text-sm font-semibold text-gray-700 block">Child Address</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="childBarangay" class="text-xs text-gray-600 font-medium">Barangay</label>
                                    <input type="text" id="childBarangay" name="child_barangay" value="{{ old('child_barangay') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Barangay">
                                </div>
                                <div class="space-y-2">
                                    <label for="childMunicipality" class="text-xs text-gray-600 font-medium">Municipality/City</label>
                                    <input type="text" id="childMunicipality" name="child_municipality" value="{{ old('child_municipality') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Municipality/City">
                                </div>
                                <div class="space-y-2">
                                    <label for="childProvince" class="text-xs text-gray-600 font-medium">Province</label>
                                    <input type="text" id="childProvince" name="child_province" value="{{ old('child_province') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Province">
                                </div>
                                <div class="space-y-2">
                                    <label for="childRegion" class="text-xs text-gray-600 font-medium">Region</label>
                                    <input type="text" id="childRegion" name="child_region" value="{{ old('child_region') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="Region">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="childHandedness" class="text-sm font-semibold text-gray-700">Child’s Handedness <span class="text-red-500">*</span></label>
                                <select id="childHandedness" name="child_handedness" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none text-sm bg-white transition">
                                    <option value="right" {{ old('child_handedness', 'right') == 'right' ? 'selected' : '' }}>Right</option>
                                    <option value="left" {{ old('child_handedness') == 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="both" {{ old('child_handedness') == 'both' ? 'selected' : '' }}>Both</option>
                                    <option value="not_yet_established" {{ old('child_handedness') == 'not_yet_established' ? 'selected' : '' }}>Not yet established</option>
                                </select>
                                @error('child_handedness') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex items-center space-x-6 pt-2 md:pt-6">
                                <span class="text-sm font-semibold text-gray-700">Is the child presently studying?</span>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="childStudyingYes" name="is_studying" value="1" {{ old('is_studying') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition">
                                        <span class="text-sm font-medium text-gray-700">Yes</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" id="childStudyingNo" {{ old('is_studying') ? '' : 'checked' }} class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition">
                                        <span class="text-sm font-medium text-gray-700">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="schoolNameField" class="space-y-2 {{ old('is_studying') ? '' : 'hidden' }}">
                            <label for="childSchool" class="text-sm font-semibold text-gray-700">School / Learning Center / Day Care</label>
                            <input type="text" id="childSchool" name="school_name" value="{{ old('school_name') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border text-sm" placeholder="School name">
                        </div>
                    </div>

                    <hr class="border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <h3 class="text-base font-bold text-gray-900 border-b pb-2 border-gray-100">Father’s Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <label for="fatherName" class="text-xs text-gray-500 font-medium mb-1 block">Full Name</label>
                                    <input type="text" id="fatherName" name="fathers_name" value="{{ old('fathers_name') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition" placeholder="Full Name">
                                </div>
                                <div>
                                    <label for="fatherAge" class="text-xs text-gray-500 font-medium mb-1 block">Age</label>
                                    <input type="number" id="fatherAge" name="fathers_age" value="{{ old('fathers_age') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition" placeholder="Age">
                                </div>
                                <div>
                                    <label for="fatherOccupation" class="text-xs text-gray-500 font-medium mb-1 block">Occupation</label>
                                    <input type="text" id="fatherOccupation" name="fathers_occupation" value="{{ old('fathers_occupation') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition" placeholder="Occupation">
                                </div>
                                <div>
                                    <label for="fatherEducation" class="text-xs text-gray-500 font-medium mb-1 block">Educational Attainment</label>
                                    <select id="fatherEducation" name="fathers_education" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition">
                                        <option value="">Select Educational Attainment</option>
                                        <option value="Elementary" {{ old('fathers_education') == 'Elementary' ? 'selected' : '' }}>Elementary</option>
                                        <option value="High School" {{ old('fathers_education') == 'High School' ? 'selected' : '' }}>High School</option>
                                        <option value="College" {{ old('fathers_education') == 'College' ? 'selected' : '' }}>College</option>
                                        <option value="Vocational" {{ old('fathers_education') == 'Vocational' ? 'selected' : '' }}>Vocational</option>
                                        <option value="Postgraduate" {{ old('fathers_education') == 'Postgraduate' ? 'selected' : '' }}>Postgraduate</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-base font-bold text-gray-900 border-b pb-2 border-gray-100">Mother’s Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <label for="motherName" class="text-xs text-gray-500 font-medium mb-1 block">Full Name</label>
                                    <input type="text" id="motherName" name="mothers_name" value="{{ old('mothers_name') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition" placeholder="Full Name">
                                </div>
                                <div>
                                    <label for="motherAge" class="text-xs text-gray-500 font-medium mb-1 block">Age</label>
                                    <input type="number" id="motherAge" name="mothers_age" value="{{ old('mothers_age') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition" placeholder="Age">
                                </div>
                                <div>
                                    <label for="motherOccupation" class="text-xs text-gray-500 font-medium mb-1 block">Occupation</label>
                                    <input type="text" id="motherOccupation" name="mothers_occupation" value="{{ old('mothers_occupation') }}" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition" placeholder="Occupation">
                                </div>
                                <div>
                                    <label for="motherEducation" class="text-xs text-gray-500 font-medium mb-1 block">Educational Attainment</label>
                                    <select id="motherEducation" name="mothers_education" class="w-full border-gray-200 rounded-xl p-3 border outline-none text-sm bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition">
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
                    </div>

                    <div class="bg-blue-50/70 p-6 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6 border border-blue-100">
                        <div class="space-y-2">
                            <label for="siblings" class="text-sm font-semibold text-blue-900">Number of Siblings</label>
                            <input type="number" id="siblings" name="number_of_siblings" value="{{ old('number_of_siblings') }}" class="w-full bg-white border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none text-sm">
                        </div>
                        <div class="space-y-2">
                            <label for="birthOrder" class="text-sm font-semibold text-blue-900">Birth Order</label>
                            <input type="number" id="birthOrder" name="birth_order" value="{{ old('birth_order') }}" class="w-full bg-white border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none text-sm">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-sm font-semibold text-gray-700 block">Child Photo Identification</label>
                        <div class="flex items-center space-x-6">
                            <div class="flex-shrink-0">
                                <div class="w-28 h-28 md:w-32 md:h-32 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center overflow-hidden">
                                    <img id="photoPreview" src="" alt="Child photo preview" class="hidden w-full h-full object-cover">
                                    <span id="photoPlaceholder" class="text-xs text-gray-400 text-center px-2">No photo selected</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <input type="file" id="photoInput" name="photo" accept="image/*" class="block w-full text-xs md:text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                <p class="mt-2 text-xs text-gray-400">JPG, PNG or GIF. Max size 2MB.</p>
                                @error('photo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 flex justify-between items-center">
                        <button type="button" id="back2" class="px-6 py-3.5 text-gray-600 font-bold hover:text-gray-900 transition text-sm">
                            Go Back
                        </button>
                        <button type="button" id="next2" class="px-10 py-3.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95 text-sm">
                            Preview Summary
                        </button>
                    </div>
                </div>

                <div id="step3" class="space-y-8 hidden">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900">Review Submission</h2>
                        <p class="text-sm text-gray-500 mt-1">Please verify all information before saving.</p>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">User Account (Login)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Email</p>
                                    <p id="reviewGuardianEmail" class="font-semibold text-gray-800 text-sm mt-0.5">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Role</p>
                                    <p class="font-semibold text-gray-800 text-sm mt-0.5">guardian</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Guardian Details</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-4">
                                <div><p class="text-xs text-gray-500 uppercase">First Name</p><p id="reviewGuardianFirst" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Middle Name</p><p id="reviewGuardianMiddle" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Last Name</p><p id="reviewGuardianLast" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Sex</p><p id="reviewGuardianSex" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Contact Number</p><p id="reviewGuardianContact" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Relationship</p><p id="reviewGuardianRelation" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                            </div>
                            <div class="mt-6 pt-4 border-t border-gray-200/60 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div><p class="text-xs text-gray-500 uppercase">Barangay</p><p id="reviewGuardianBarangay" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Municipality/City</p><p id="reviewGuardianMunicipality" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Province</p><p id="reviewGuardianProvince" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Region</p><p id="reviewGuardianRegion" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 space-y-6">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Child Profile</h3>

                            <div class="flex flex-col md:flex-row gap-6">
                                <img id="reviewPhoto" src="" alt="Preview" class="hidden w-28 h-28 rounded-xl object-cover border-2 border-white shadow-sm bg-gray-200 flex-shrink-0">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-4">
                                    <div class="col-span-1 md:col-span-2">
                                        <p class="text-xs text-gray-500 uppercase">Full Name</p>
                                        <p class="font-semibold text-gray-800 text-sm mt-0.5"><span id="reviewChildFirst"></span> <span id="reviewChildMiddle"></span> <span id="reviewChildLast"></span></p>
                                    </div>
                                    <div><p class="text-xs text-gray-500 uppercase">Sex</p><p id="reviewChildSex" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                    <div><p class="text-xs text-gray-500 uppercase">Date of Birth</p><p id="reviewChildDob" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>

                                    <div><p class="text-xs text-gray-500 uppercase">Barangay</p><p id="reviewChildBarangay" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                    <div><p class="text-xs text-gray-500 uppercase">Municipality/City</p><p id="reviewChildMunicipality" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                    <div><p class="text-xs text-gray-500 uppercase">Province</p><p id="reviewChildProvince" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                    <div><p class="text-xs text-gray-500 uppercase">Region</p><p id="reviewChildRegion" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>

                                    <div><p class="text-xs text-gray-500 uppercase">Handedness</p><p id="reviewChildHandedness" class="font-semibold text-gray-800 text-sm mt-0.5 capitalize">-</p></div>
                                    <div><p class="text-xs text-gray-500 uppercase">Currently Studying</p><p id="reviewChildIsStudying" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                    <div class="col-span-1 md:col-span-2"><p class="text-xs text-gray-500 uppercase">School Name</p><p id="reviewChildSchool" class="font-semibold text-gray-800 text-sm mt-0.5">-</p></div>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-gray-200/60 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-xs font-bold text-blue-600 uppercase mb-3">Father's Info</p>
                                    <div class="space-y-2">
                                        <div><p class="text-xs text-gray-500 uppercase">Full Name</p><p id="reviewFatherName" class="font-semibold text-gray-800 text-sm">-</p></div>
                                        <div><p class="text-xs text-gray-500 uppercase">Age</p><p id="reviewFatherAge" class="font-semibold text-gray-800 text-sm">-</p></div>
                                        <div><p class="text-xs text-gray-500 uppercase">Occupation</p><p id="reviewFatherOccupation" class="font-semibold text-gray-800 text-sm">-</p></div>
                                        <div><p class="text-xs text-gray-500 uppercase">Education</p><p id="reviewFatherEducation" class="font-semibold text-gray-800 text-sm">-</p></div>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-bold text-pink-600 uppercase mb-3">Mother's Info</p>
                                    <div class="space-y-2">
                                        <div><p class="text-xs text-gray-500 uppercase">Full Name</p><p id="reviewMotherName" class="font-semibold text-gray-800 text-sm">-</p></div>
                                        <div><p class="text-xs text-gray-500 uppercase">Age</p><p id="reviewMotherAge" class="font-semibold text-gray-800 text-sm">-</p></div>
                                        <div><p class="text-xs text-gray-500 uppercase">Occupation</p><p id="reviewMotherOccupation" class="font-semibold text-gray-800 text-sm">-</p></div>
                                        <div><p class="text-xs text-gray-500 uppercase">Education</p><p id="reviewMotherEducation" class="font-semibold text-gray-800 text-sm">-</p></div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200/60 flex space-x-8">
                                <div><p class="text-xs text-gray-500 uppercase">Siblings</p><p id="reviewSiblings" class="font-semibold text-gray-800 text-sm">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Birth Order</p><p id="reviewBirthOrder" class="font-semibold text-gray-800 text-sm">-</p></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 flex justify-between items-center">
                        <button type="button" id="back3" class="px-6 py-3.5 text-gray-600 font-bold hover:text-gray-900 transition text-sm">
                            Edit Details
                        </button>
                        <button type="button" id="saveBtn" class="px-12 py-3.5 bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-100 hover:bg-green-700 transition transform active:scale-95 text-sm">
                            Confirm & Save
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const steps = [document.getElementById('step1'), document.getElementById('step2'), document.getElementById('step3')];
    const labels = [document.getElementById('label-step1'), document.getElementById('label-step2'), document.getElementById('label-step3')];
    const progressBar = document.getElementById('progress-bar');
    const form = document.getElementById('guardianRegistrationForm');

    function goToStep(stepNumber) {
        const index = stepNumber - 1;
        const progressWidths = ['33%', '66%', '100%'];
        if (progressBar) progressBar.style.width = progressWidths[index];

        steps.forEach((step, i) => {
            if (step) step.classList.toggle('hidden', i !== index);
        });

        labels.forEach((label, i) => {
            if (!label) return;
            if (i === index) {
                label.className = "transition-all duration-300 text-[10px] md:text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-blue-600 text-white shadow-sm";
            } else if (i < index) {
                label.className = "transition-all duration-300 text-[10px] md:text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-blue-100 text-blue-700";
            } else {
                label.className = "transition-all duration-300 text-[10px] md:text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-gray-200 text-gray-500";
            }
        });

        const formContainer = document.getElementById('form-container');
        if (formContainer) formContainer.scrollTop = 0;
    }

    @if ($errors->has('child_first_name') || $errors->has('child_last_name') || $errors->has('child_sex') || $errors->has('child_date_of_birth') || $errors->has('child_handedness') || $errors->has('photo'))
        goToStep(2);
    @else
        goToStep(1);
    @endif

    document.getElementById('next1')?.addEventListener('click', () => {
        const reqInputs = document.querySelectorAll('#step1 input[required], #step1 select[required]');
        let valid = true;
        reqInputs.forEach(input => {
            if (!input.checkValidity()) {
                input.reportValidity();
                valid = false;
                return false;
            }
        });
        if (valid) goToStep(2);
    });

    document.getElementById('back2')?.addEventListener('click', () => goToStep(1));
    
    document.getElementById('next2')?.addEventListener('click', () => {
        const reqInputs = document.querySelectorAll('#step2 input[required], #step2 select[required]');
        let valid = true;
        reqInputs.forEach(input => {
            if (!input.checkValidity()) {
                input.reportValidity();
                valid = false;
                return false;
            }
        });
        if (valid) {
            updateReview();
            goToStep(3);
        }
    });

    document.getElementById('back3')?.addEventListener('click', () => goToStep(2));

    // Confirm and Save
    document.getElementById('saveBtn')?.addEventListener('click', () => {
        if (confirm("Are you sure you want to confirm and save this record?")) {
            form.submit();
        }
    });

    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const reviewPhoto = document.getElementById('reviewPhoto');
    const photoPlaceholder = document.getElementById('photoPlaceholder');

    photoInput?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                if (photoPreview) {
                    photoPreview.src = ev.target.result;
                    photoPreview.classList.remove('hidden');
                }
                if (reviewPhoto) {
                    reviewPhoto.src = ev.target.result;
                    reviewPhoto.classList.remove('hidden');
                }
                if (photoPlaceholder) photoPlaceholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    const yesBox = document.getElementById('childStudyingYes');
    const noBox = document.getElementById('childStudyingNo');
    const schoolField = document.getElementById('schoolNameField');

    yesBox?.addEventListener('change', () => {
        if (yesBox.checked) {
            if (noBox) noBox.checked = false;
            schoolField?.classList.remove('hidden');
        } else {
            schoolField?.classList.add('hidden');
        }
    });

    noBox?.addEventListener('change', () => {
        if (noBox.checked) {
            if (yesBox) yesBox.checked = false;
            schoolField?.classList.add('hidden');
        }
    });

    function updateReview() {
        const fields = {
            guardianFirst: "reviewGuardianFirst",
            guardianMiddle: "reviewGuardianMiddle",
            guardianLast: "reviewGuardianLast",
            guardianSex: "reviewGuardianSex",
            guardianContact: "reviewGuardianContact",
            guardianRelation: "reviewGuardianRelation",
            guardianBarangay: "reviewGuardianBarangay",
            guardianMunicipality: "reviewGuardianMunicipality",
            guardianProvince: "reviewGuardianProvince",
            guardianRegion: "reviewGuardianRegion",
            guardianEmail: "reviewGuardianEmail",

            childFirst: "reviewChildFirst",
            childMiddle: "reviewChildMiddle",
            childLast: "reviewChildLast",
            childSex: "reviewChildSex",
            childDob: "reviewChildDob",
            childBarangay: "reviewChildBarangay",
            childMunicipality: "reviewChildMunicipality",
            childProvince: "reviewChildProvince",
            childRegion: "reviewChildRegion",
            childHandedness: "reviewChildHandedness",
            childSchool: "reviewChildSchool",
            fatherName: "reviewFatherName",
            fatherAge: "reviewFatherAge",
            fatherOccupation: "reviewFatherOccupation",
            fatherEducation: "reviewFatherEducation",
            motherName: "reviewMotherName",
            motherAge: "reviewMotherAge",
            motherOccupation: "reviewMotherOccupation",
            motherEducation: "reviewMotherEducation",
            siblings: "reviewSiblings",
            birthOrder: "reviewBirthOrder"
        };

        Object.entries(fields).forEach(([inputId, reviewId]) => {
            const input = document.getElementById(inputId);
            const review = document.getElementById(reviewId);
            if (input && review) {
                review.textContent = input.value?.trim() || '-';
            }
        });

        const reviewStudying = document.getElementById('reviewChildIsStudying');
        if (reviewStudying) {
            reviewStudying.textContent = yesBox?.checked ? 'Yes' : (noBox?.checked ? 'No' : 'No');
        }
    }
});
</script>
@endsection