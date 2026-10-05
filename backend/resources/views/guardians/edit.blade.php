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

        <div id="error-alert" class="hidden p-6 bg-red-50 border-b border-red-100 text-sm text-red-600">
            <p class="font-bold">Please correct the highlighted errors below.</p>
        </div>

        <div id="form-container" class="p-8 md:p-12">
            <form id="editGuardianForm" method="POST" action="{{ route('guardians.update', $guardian->id) }}" class="space-y-10">
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
                            <p id="error-email" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Role</label>
                            <input type="text" value="guardian" readonly 
                                class="w-full border-gray-100 bg-gray-50 text-gray-500 rounded-xl p-3 cursor-not-allowed border">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">New Password <span class="text-gray-400 text-xs">(optional)</span></label>
                            <input type="password" id="guardianPassword" name="password"
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border" 
                                placeholder="Leave blank to keep current password">
                            <p id="error-password" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Confirm New Password</label>
                            <input type="password" id="guardianConfirmPassword" name="password_confirmation"
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-password_confirmation" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Guardian Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">First Name</label>
                            <input type="text" id="guardianFirst" name="first_name" value="{{ old('first_name', $guardian->first_name ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-first_name" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" id="guardianMiddle" name="middle_name" value="{{ old('middle_name', $guardian->middle_name ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-middle_name" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Last Name</label>
                            <input type="text" id="guardianLast" name="last_name" value="{{ old('last_name', $guardian->last_name ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-last_name" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
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
                            <p id="error-sex" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Contact Number</label>
                            <input type="tel" id="guardianContact" name="contact_number" value="{{ old('contact_number', $guardian->contact_number ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-contact_number" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Relationship to Child</label>
                            <input type="text" id="guardianRelation" name="relationship_to_child" value="{{ old('relationship_to_child', $guardian->relationship_to_child ?? '') }}" 
                                class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-relationship_to_child" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Guardian Address</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Barangay</label>
                            <input type="text" id="guardianBarangay" name="barangay" value="{{ old('barangay', $guardian->barangay ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-barangay" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Municipality/City</label>
                            <input type="text" id="guardianMunicipality" name="municipality" value="{{ old('municipality', $guardian->municipality ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-municipality" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Province</label>
                            <input type="text" id="guardianProvince" name="province" value="{{ old('province', $guardian->province ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-province" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-gray-600">Region</label>
                            <input type="text" id="guardianRegion" name="region" value="{{ old('region', $guardian->region ?? '') }}" class="w-full border-gray-200 rounded-xl p-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none border">
                            <p id="error-region" class="text-xs font-semibold text-red-600 mt-1 hidden"></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-8 border-t border-gray-100">
                    <a href="{{ route('guardians.index') }}" 
                       class="px-8 py-3 text-gray-600 font-semibold hover:text-gray-900 transition">Cancel</a>
                    <button type="submit" id="save-btn"
                            class="px-10 py-3 bg-green-600 text-white font-semibold rounded-xl shadow-lg shadow-green-100 hover:bg-green-700 transition transform active:scale-95">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div id="success-message" class="hidden p-12 text-center animate-fade-in">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Changes Saved Successfully</h3>
                <p class="text-gray-600 mb-8">The guardian information has been successfully updated in the system records.</p>
                <a href="{{ route('guardians.index') }}" 
                   class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                    Return to Guardians List
                </a>
            </div>
        </div>

        <div id="failed-message" class="hidden p-12 text-center animate-fade-in">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-red-100 text-red-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✕</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Update Failed</h3>
                <p class="text-gray-600 mb-8">Something went wrong while applying the updates. Please check your data fields and try again.</p>
                <button type="button" onclick="resetFormLayout()" 
                   class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                    Go Back and Try Again
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    document.getElementById('editGuardianForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const userConfirmed = confirm("Are you sure you want to save these changes?");
        if (!userConfirmed) return;

        const form = this;
        const submitBtn = document.getElementById('save-btn');
        const formHeader = document.getElementById('form-header');
        const formContainer = document.getElementById('form-container');
        const errorAlert = document.getElementById('error-alert');
        const successMessage = document.getElementById('success-message');
        const failedMessage = document.getElementById('failed-message');

        // Prevent rapid double-clicks
        if (submitBtn.disabled) return;

        // Reset display states
        submitBtn.disabled = true;
        submitBtn.innerText = "Processing...";
        errorAlert.classList.add('hidden');
        
        form.querySelectorAll('input, select').forEach(element => {
            element.classList.remove('border-red-500', 'focus:ring-red-500/10', 'focus:border-red-500');
            element.classList.add('border-gray-200', 'focus:ring-blue-500/10', 'focus:border-blue-500');
        });
        form.querySelectorAll('p[id^="error-"]').forEach(p => {
            p.innerText = '';
            p.classList.add('hidden');
        });

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const isJson = response.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await response.json() : null;

            if (!response.ok) {
                if (response.status === 422 && data && data.errors) {
                    errorAlert.classList.remove('hidden');
                    
                    Object.keys(data.errors).forEach(fieldName => {
                        const errorMsg = data.errors[fieldName][0];
                        const errorParagraph = document.getElementById(`error-${fieldName}`);
                        const inputElement = form.querySelector(`[name="${fieldName}"]`);
                        
                        if (inputElement) {
                            inputElement.classList.remove('border-gray-200', 'focus:ring-blue-500/10', 'focus:border-blue-500');
                            inputElement.classList.add('border-red-500', 'focus:ring-red-500/10', 'focus:border-red-500');
                        }
                        
                        if (errorParagraph) {
                            errorParagraph.innerText = errorMsg;
                            errorParagraph.classList.remove('hidden');
                        }
                    });
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    formHeader.classList.add('hidden');
                    formContainer.classList.add('hidden');
                    failedMessage.classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                return;
            }

            formHeader.classList.add('hidden');
            formContainer.classList.add('hidden');
            successMessage.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(err => {
            console.error(err);
            formHeader.classList.add('hidden');
            formContainer.classList.add('hidden');
            failedMessage.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerText = "Save Changes";
        });
    });

    // Clean up input fields instantly when the user revises their typing
    document.getElementById('editGuardianForm').querySelectorAll('input, select').forEach(element => {
        element.addEventListener('input', function() {
            this.classList.remove('border-red-500', 'focus:ring-red-500/10', 'focus:border-red-500');
            this.classList.add('border-gray-200', 'focus:ring-blue-500/10', 'focus:border-blue-500');
            
            const p = document.getElementById(`error-${this.name}`);
            if (p) {
                p.innerText = '';
                p.classList.add('hidden');
            }

            const activeErrors = document.getElementById('editGuardianForm').querySelectorAll('.border-red-500');
            if (activeErrors.length === 0) {
                document.getElementById('error-alert').classList.add('hidden');
            }
        });
    });

    function resetFormLayout() {
        document.getElementById('form-header').classList.remove('hidden');
        document.getElementById('form-container').classList.remove('hidden');
        document.getElementById('failed-message').classList.add('hidden');
        document.getElementById('error-alert').classList.add('hidden');
    }
</script>
@endsection