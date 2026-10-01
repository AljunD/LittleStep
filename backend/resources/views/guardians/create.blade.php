@extends('components.app')

@section('title', 'Add Guardian')

@section('content')
<div class="max-w-4xl mx-auto my-10">
    <div class="mb-6">
        <a href="{{ route('guardians.index') }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Guardians
        </a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Header -->
        <div class="px-8 py-8 bg-gray-50 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Registration Portal</h2>
                
                <div class="flex items-center space-x-3 mt-4 md:mt-0">
                    <span id="label-step1" class="transition-all duration-300 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-blue-600 text-white shadow-sm">Step 1</span>
                    <div class="h-px w-4 bg-gray-300"></div>
                    <span id="label-step2" class="transition-all duration-300 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-gray-200 text-gray-500">Step 2</span>
                    <div class="h-px w-4 bg-gray-300"></div>
                    <span id="label-step3" class="transition-all duration-300 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-gray-200 text-gray-500">Step 3</span>
                </div>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out" style="width: 33%;"></div>
            </div>
        </div>

        <!-- Success Message -->
        <div id="success-message" class="hidden p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Registration Complete</h3>
                <p class="text-gray-600 mb-8">The guardian account, guardian profile, and child profile have been successfully created.</p>
                <a href="{{ route('guardians.index') }}" class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                    Done
                </a>
            </div>
        </div>

        <!-- Form Content -->
        <div id="form-container" class="p-8 md:p-12 max-h-[calc(100vh-240px)] overflow-y-auto custom-scroll">
            
            <!-- Step 1: Guardian -->
            <div id="step1" class="space-y-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Guardian Account & Information</h2>
                    <p class="text-gray-500 mt-1">Login credentials and personal details of the guardian.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">First Name</label>
                        <input type="text" id="guardianFirst" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="e.g. Maria">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                        <input type="text" id="guardianMiddle" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="e.g. Lopez">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Last Name</label>
                        <input type="text" id="guardianLast" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="e.g. Santos">
                    </div>
                </div>
                    <div class="space-y-2 mt-6">
                        <label class="text-sm font-semibold text-gray-700">Sex</label>
                        <select id="guardianSex" name="sex"
                            class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                        <option value="" disabled>Select sex</option>
                        <option value="Male" {{ old('sex', $guardian->sex ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex', $guardian->sex ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Contact Number</label>
                        <input type="tel" id="guardianContact" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="0917 123 4567">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Relationship to Child</label>
                        <input type="text" id="guardianRelation" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Mother, Father, Legal Guardian, etc.">
                    </div>
                </div>

                <!-- Guardian Address -->
                <div class="space-y-4">
                    <label class="text-sm font-semibold text-gray-700">Guardian Address</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Barangay</label>
                            <input type="text" id="guardianBarangay" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Barangay">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Municipality/City</label>
                            <input type="text" id="guardianMunicipality" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Municipality/City">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Province</label>
                            <input type="text" id="guardianProvince" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Province">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Region</label>
                            <input type="text" id="guardianRegion" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Region">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" id="guardianEmail" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="name@example.com">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                        <input type="password" id="guardianPassword" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" id="guardianConfirmPassword" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">Account Role</label>
                    <input type="text" class="w-full border-gray-100 rounded-xl p-3 bg-gray-50 text-gray-500 cursor-not-allowed border" value="guardian" readonly>
                </div>

                <div class="pt-6 flex justify-end">
                    <button id="next1" class="px-12 py-4 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95">Next Step</button>
                </div>
            </div>

            <!-- Step 2: child Profile -->
            <div id="step2" class="space-y-10 hidden">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Child Profile</h2>
                    <p class="text-gray-500 mt-1">Sociodemographic and family background.</p>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">First Name</label>
                            <input type="text" id="childFirst" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" id="childMiddle" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Last Name</label>
                            <input type="text" id="childLast" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Sex</label>
                            <select id="childSex" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none appearance-none bg-white">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Date of Birth</label>
                            <input type="date" id="childDob" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                        </div>
                    </div>

                    <!-- Child Address -->
                    <div class="space-y-4">
                        <label class="text-sm font-semibold text-gray-700">Child Address</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Barangay</label>
                                <input type="text" id="childBarangay" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Barangay">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Municipality/City</label>
                                <input type="text" id="childMunicipality" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Municipality/City">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Province</label>
                                <input type="text" id="childProvince" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Province">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm text-gray-600">Region</label>
                                <input type="text" id="childRegion" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="Region">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Child’s Handedness</label>
                            <select id="childHandedness" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none appearance-none bg-white">
                                <option value="right">Right</option>
                                <option value="left">Left</option>
                                <option value="both">Both</option>
                                <option value="not_yet_established">Not yet established</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-6 pt-8">
                            <label class="text-sm font-semibold text-gray-700">Is the child presently studying?</label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" id="childStudyingYes" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition">
                                <span class="text-sm font-medium text-gray-700">Yes</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" id="childStudyingNo" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition">
                                <span class="text-sm font-medium text-gray-700">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="schoolNameField" class="space-y-2 hidden">
                        <label class="text-sm font-semibold text-gray-700">School / Learning Center / Day Care</label>
                        <input type="text" id="childSchool" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" placeholder="School name">
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Family Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Father’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" id="fatherName" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name">
                            <input type="number" id="fatherAge" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age">
                            <input type="text" id="fatherOccupation" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation">

                            <!-- Drop-down for Educational Attainment -->
                            <select id="fatherEducation" class="w-full border-gray-200 rounded-xl p-3 border outline-none">
                                <option value="">Select Educational Attainment</option>
                                <option value="Elementary">Elementary</option>
                                <option value="High School">High School</option>
                                <option value="College">College</option>
                                <option value="Vocational">Vocational</option>
                                <option value="Postgraduate">Postgraduate</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900">Mother’s Information</h3>
                        <div class="space-y-4">
                            <input type="text" id="motherName" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Full Name">
                            <input type="number" id="motherAge" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Age">
                            <input type="text" id="motherOccupation" class="w-full border-gray-200 rounded-xl p-3 border outline-none" placeholder="Occupation">

                            <!-- Drop-down for Educational Attainment -->
                            <select id="motherEducation" class="w-full border-gray-200 rounded-xl p-3 border outline-none">
                                <option value="">Select Educational Attainment</option>
                                <option value="Elementary">Elementary</option>
                                <option value="High School">High School</option>
                                <option value="College">College</option>
                                <option value="Vocational">Vocational</option>
                                <option value="Postgraduate">Postgraduate</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6 border border-blue-100">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Number of Siblings</label>
                        <input type="number" id="siblings" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-blue-900">Birth Order</label>
                        <input type="number" id="birthOrder" class="w-full border-blue-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 border outline-none">
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-sm font-semibold text-gray-700">Child Photo Identification</label>
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                                <img id="photoPreview" src="" class="hidden w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="photoInput" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="mt-2 text-xs text-gray-400">JPG, PNG or GIF. Max size 2MB.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-8 flex justify-between">
                    <button id="back2" class="px-8 py-4 text-gray-600 font-bold hover:text-gray-900 transition">Go Back</button>
                    <button id="next2" class="px-12 py-4 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform active:scale-95">Preview Summary</button>
                </div>
            </div>

            <!-- Step 3: Review -->
            <div id="step3" class="space-y-10 hidden">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Review Submission</h2>
                    <p class="text-gray-500 mt-1">Please verify all information before saving.</p>
                </div>

                <div class="grid grid-cols-1 gap-8">
                    <!-- User Account -->
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">User Account (Login)</h3>
                        <div class="grid grid-cols-2 gap-y-4">
                            <div><p class="text-xs text-gray-500 uppercase">Email</p><p id="reviewGuardianEmail" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Role</p><p class="font-semibold text-gray-800">guardian</p></div>
                        </div>
                    </div>

                    <!-- Guardian Details -->
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Guardian Details</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-2">
                            <div><p class="text-xs text-gray-500 uppercase">First Name</p><p id="reviewGuardianFirst" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Middle Name</p><p id="reviewGuardianMiddle" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Last Name</p><p id="reviewGuardianLast" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Sex</p><p id="reviewGuardianSex" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Contact Number</p><p id="reviewGuardianContact" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Relationship</p><p id="reviewGuardianRelation" class="font-semibold text-gray-800">-</p></div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><p class="text-xs text-gray-500 uppercase">Barangay</p><p id="reviewGuardianBarangay" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Municipality/City</p><p id="reviewGuardianMunicipality" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Province</p><p id="reviewGuardianProvince" class="font-semibold text-gray-800">-</p></div>
                            <div><p class="text-xs text-gray-500 uppercase">Region</p><p id="reviewGuardianRegion" class="font-semibold text-gray-800">-</p></div>
                        </div>
                    </div>

                    <!-- Child Details -->
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-6">Child Profile</h3>

                        <div class="flex flex-col md:flex-row gap-8">
                            <img id="reviewPhoto" src="" alt="Preview" class="hidden w-32 h-32 rounded-xl object-cover border-4 border-white shadow-sm bg-gray-200">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-4">
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-500 uppercase">Full Name</p>
                                    <p class="font-semibold text-gray-800"><span id="reviewChildFirst"></span> <span id="reviewChildMiddle"></span> <span id="reviewChildLast"></span></p>
                                </div>
                                <div><p class="text-xs text-gray-500 uppercase">Sex</p><p id="reviewChildSex" class="font-semibold text-gray-800">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Date of Birth</p><p id="reviewChildDob" class="font-semibold text-gray-800">-</p></div>

                                <!-- Child Address Review -->
                                <div><p class="text-xs text-gray-500 uppercase">Barangay</p><p id="reviewChildBarangay" class="font-semibold text-gray-800">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Municipality/City</p><p id="reviewChildMunicipality" class="font-semibold text-gray-800">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Province</p><p id="reviewChildProvince" class="font-semibold text-gray-800">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Region</p><p id="reviewChildRegion" class="font-semibold text-gray-800">-</p></div>

                                <div><p class="text-xs text-gray-500 uppercase">Handedness</p><p id="reviewChildHandedness" class="font-semibold text-gray-800">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">Currently Studying</p><p id="reviewChildIsStudying" class="font-semibold text-gray-800">-</p></div>
                                <div><p class="text-xs text-gray-500 uppercase">School Name</p><p id="reviewChildSchool" class="font-semibold text-gray-800">-</p></div>
                            </div>
                        </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Father -->
                        <div>
                            <p class="text-xs font-bold text-blue-600 uppercase mb-2">Father</p>
                            <div class="space-y-2">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Full Name</p>
                                    <p id="reviewFatherName" class="font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Age</p>
                                    <p id="reviewFatherAge" class="font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Occupation</p>
                                    <p id="reviewFatherOccupation" class="font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Educational Attainment</p>
                                    <p id="reviewFatherEducation" class="font-semibold text-gray-800">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mother -->
                        <div>
                            <p class="text-xs font-bold text-pink-600 uppercase mb-2">Mother</p>
                            <div class="space-y-2">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Full Name</p>
                                    <p id="reviewMotherName" class="font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Age</p>
                                    <p id="reviewMotherAge" class="font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Occupation</p>
                                    <p id="reviewMotherOccupation" class="font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Educational Attainment</p>
                                    <p id="reviewMotherEducation" class="font-semibold text-gray-800">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Siblings & Birth Order -->
                    <div class="mt-6 flex space-x-8">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Siblings</p>
                            <p id="reviewSiblings" class="font-semibold text-gray-800">-</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Birth Order</p>
                            <p id="reviewBirthOrder" class="font-semibold text-gray-800">-</p>
                        </div>
                    </div>

                <div class="pt-8 flex justify-between">
                    <button id="back3" class="px-8 py-4 text-gray-600 font-bold hover:text-gray-900 transition">Edit Details</button>
                    <button id="saveBtn" class="px-16 py-4 bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-100 hover:bg-green-700 transition transform active:scale-95">Confirm & Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const steps = [document.getElementById('step1'), document.getElementById('step2'), document.getElementById('step3')];
    const labels = [document.getElementById('label-step1'), document.getElementById('label-step2'), document.getElementById('label-step3')];
    const progressBar = document.getElementById('progress-bar');
    const successMessage = document.getElementById('success-message');
    const formContainer = document.getElementById('form-container');

    function goToStep(stepNumber) {
        const index = stepNumber - 1;
        const progressWidths = ['33%', '66%', '100%'];
        progressBar.style.width = progressWidths[index];

        steps.forEach((step, i) => step.classList.toggle('hidden', i !== index));

        labels.forEach((label, i) => {
            if (i === index) {
                label.className = "text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-blue-600 text-white shadow-sm transition-all duration-300";
            } else if (i < index) {
                label.className = "text-xs font-bold uppercase tracking-wider text-blue-600 transition-all duration-300";
            } else {
                label.className = "text-xs font-bold uppercase tracking-wider text-gray-400 transition-all duration-300";
            }
        });
    }

    document.getElementById('next1')?.addEventListener('click', () => goToStep(2));
    document.getElementById('back2')?.addEventListener('click', () => goToStep(1));
    document.getElementById('next2')?.addEventListener('click', () => { updateReview(); goToStep(3); });
    document.getElementById('back3')?.addEventListener('click', () => goToStep(2));

    // Confirm and Save with confirmation message
    document.getElementById('saveBtn')?.addEventListener('click', () => {
        const userConfirmed = confirm("Are you sure you want to confirm and save this record?");
        if (userConfirmed) {
            formContainer.classList.add('hidden');
            successMessage.classList.remove('hidden');
            progressBar.style.width = '100%';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            alert("Save action was cancelled.");
        }
    });

    // Photo Preview
    document.getElementById('photoInput')?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                document.getElementById('photoPreview').src = ev.target.result;
                document.getElementById('reviewPhoto').src = ev.target.result;
                document.getElementById('photoPreview').classList.remove('hidden');
                document.getElementById('reviewPhoto').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Studying Toggle
    const yesBox = document.getElementById('childStudyingYes');
    const noBox = document.getElementById('childStudyingNo');
    const schoolField = document.getElementById('schoolNameField');

    yesBox?.addEventListener('change', () => {
        if (yesBox.checked) {
            noBox.checked = false;
            schoolField.classList.remove('hidden');
        }
    });

    noBox?.addEventListener('change', () => {
        if (noBox.checked) {
            yesBox.checked = false;
            schoolField.classList.add('hidden');
        }
    });

    // Update Review Function
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
            if (input && review) review.textContent = input.value?.trim() || '-';
        });

        // Studying Status
        const reviewStudying = document.getElementById('reviewChildIsStudying');
        if (reviewStudying) {
            reviewStudying.textContent = yesBox?.checked ? 'Yes' : (noBox?.checked ? 'No' : '-');
        }
    }
</script>

@endsection