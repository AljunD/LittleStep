@extends('components.app')

@section('title', 'Observation Notes')

@section('content')
<div class="max-w-4xl mx-auto my-10 bg-white shadow-sm border border-gray-200 text-gray-900 font-serif overflow-hidden">
    
    <x-validation-errors class="mx-10 mt-8 font-sans" />

    <div id="observation-form-container" class="p-10 space-y-10">
        <form id="observation-form" class="space-y-10" method="POST" action="{{ route('progress.observation.store') }}">
            @csrf
            <input type="hidden" name="child_id" value="{{ $child->id }}">
            
            <div class="space-y-3 font-mono text-sm">
                <div class="flex items-baseline">
                    <span class="whitespace-nowrap mr-2">Name of examiner :</span>
                    <input type="text" value="{{ trim(implode(' ', array_filter([$teacher->first_name, $teacher->middle_name, $teacher->last_name]))) }}" readonly class="flex-1 bg-transparent border-b border-dashed border-gray-400 px-2 font-sans py-0.5">
                </div>
                <div class="flex items-baseline">
                    <span class="whitespace-nowrap mr-2">Date administered :</span>
                    <input type="date" name="date_administered" value="{{ old('date_administered', $progressRecord?->evaluation_date ?? now()->toDateString()) }}" required class="flex-1 bg-transparent border-b border-dashed border-gray-400 focus:outline-none focus:border-blue-500 px-2 font-sans py-0.5">
                </div>
                <div class="flex items-baseline">
                    <span class="whitespace-nowrap mr-2">Place where test is administered :</span>
                    <input type="text" name="place_administered" value="{{ old('place_administered', $progressRecord?->domainObservation?->place_administered) }}" class="flex-1 bg-transparent border-b border-dashed border-gray-400 focus:outline-none focus:border-blue-500 px-2 font-sans py-0.5">
                </div>
                <p class="text-xs text-gray-500">Child: {{ $child->first_name }} {{ $child->last_name }}</p>
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
                    <textarea name="background_observations" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide">{{ old('background_observations', $progressRecord?->domainObservation?->background_observations) }}</textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Family environment (ex. Health of family members/family problems/ economic conditions/etc.)
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="family_environment_notes" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide">{{ old('family_environment_notes', $progressRecord?->domainObservation?->family_environment_notes) }}</textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Parents’ stimulating activities for the child (What are the activities/things that the parents do to help stimulate the child’s development?)
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="stimulating_activities_notes" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide">{{ old('stimulating_activities_notes', $progressRecord?->domainObservation?->stimulating_activities_notes) }}</textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Home environment (ex. Facilities/type of house/ household items/interaction/etc.)
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="home_environment_notes" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide">{{ old('home_environment_notes', $progressRecord?->domainObservation?->home_environment_notes) }}</textarea>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-sans font-medium text-gray-800">
                    Others
                </label>
                <div class="relative bg-transparent" style="background-image: linear-gradient(to bottom, transparent 31px, #9ca3af 32px); background-size: 100% 32px; line-height: 32px;">
                    <textarea name="other_notes" rows="5" class="w-full bg-transparent border-none resize-none focus:outline-none focus:ring-0 p-0 font-sans text-sm leading-[32px] tracking-wide">{{ old('other_notes', $progressRecord?->domainObservation?->other_notes) }}</textarea>
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

</div>
@endsection