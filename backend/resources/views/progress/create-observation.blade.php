@extends('components.app')

@section('title', 'Observation Notes')

@section('content')
<div class="max-w-4xl mx-auto my-10 bg-white shadow-sm border border-gray-200 text-gray-900 font-serif overflow-hidden">
    
    <div id="observation-form-container" class="p-10 space-y-10">
        <form id="static-observation-form" class="space-y-10" onsubmit="handleStaticSubmit(event)">
            
            <div class="space-y-3 font-mono text-sm">
                <div class="flex items-baseline">
                    <span class="whitespace-nowrap mr-2">Name of examiner :</span>
                    <input type="text" name="examiner_name" class="flex-1 bg-transparent border-b border-dashed border-gray-400 focus:outline-none focus:border-blue-500 px-2 font-sans py-0.5">
                </div>
                <div class="flex items-baseline">
                    <span class="whitespace-nowrap mr-2">Date administered :</span>
                    <input type="date" name="date_administered" class="flex-1 bg-transparent border-b border-dashed border-gray-400 focus:outline-none focus:border-blue-500 px-2 font-sans py-0.5">
                </div>
                <div class="flex items-baseline">
                    <span class="whitespace-nowrap mr-2">Place where test is administered :</span>
                    <input type="text" name="place_administered" class="flex-1 bg-transparent border-b border-dashed border-gray-400 focus:outline-none focus:border-blue-500 px-2 font-sans py-0.5">
                </div>
            </div>

            <div class="text-[13px] font-sans text-gray-700 space-y-1">
                <p class="font-medium">To the examiner :</p>
                <p>Please fill out the spaces below for additional information. Thank you very much.</p>
                <p>Write down your notes, descriptions and observations on the following points:</p>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Child’s background (ex. behavior/health/etc.)
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="child_background" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide"></textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Family environment (ex. Health of family members/family problems/ economic conditions/etc.)
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="family_environment" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide"></textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Parents’ stimulating activities for the child (What are the activities/things that the parents do to help stimulate the child’s development?)
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="parents_activities" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide"></textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Home environment (ex. Facilities/type of house/ household items/interaction/etc.)
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="home_environment" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide"></textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Others
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="others_notes" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide"></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100 print:hidden">
                <a href="{{ route('progress.index') }}"
                   class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-gray-200 transition font-sans">
                    Cancel
                </a>
                <button type="submit" id="save-button"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-blue-700 transition shadow-sm font-sans">
                    Save Observations
                </button>
            </div>
        </form>
    </div>

    <div id="success-message" class="hidden p-12 text-center font-sans">
        <div class="max-w-md mx-auto">
            <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Observation Saved Successfully</h3>
            <p class="text-gray-600 mb-8">The observation has been recorded for the child.</p>
            <a href="{{ route('progress.index') }}" 
               class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                Return to Progress Records
            </a>
        </div>
    </div>

    <div id="failed-message" class="hidden p-12 text-center font-sans">
        <div class="max-w-md mx-auto">
            <div class="mb-6 bg-red-100 text-red-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✕</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Observation Save Failed</h3>
            <p class="text-gray-600 mb-8">Something went wrong while saving the observation. Please try again.</p>
            <a href="{{ route('progress.index') }}" 
               class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95">
                Back to Progress Records
            </a>
        </div>
    </div>
</div>

<script>
    function handleStaticSubmit(event) {
        event.preventDefault(); // Stop standard form refresh
        
        const saveButton = document.getElementById('save-button');
        const formContainer = document.getElementById('observation-form-container');
        const successView = document.getElementById('success-message');
        const failedView = document.getElementById('failed-message');

        // Optional UX: Update button state to simulate processing
        saveButton.disabled = true;
        saveButton.innerText = "Saving...";

        setTimeout(() => {
            // Hide the active form layout container
            formContainer.classList.add('hidden');

            // Static simulation rule: 80% chance success, 20% failure
            const isSuccessful = Math.random() > 0.2;

            if (isSuccessful) {
                successView.classList.remove('hidden');
            } else {
                failedView.classList.remove('hidden');
            }
        }, 600); // 600ms artificial database latency delay
    }
</script>
@endsection