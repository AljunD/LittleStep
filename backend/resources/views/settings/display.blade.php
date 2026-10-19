@extends('settings.layout')

@section('title', 'Display Settings')

@php
    // Fixed hex classes (page, card, text line) so each preview keeps its own look whichever theme is active.
    $lightPreview = ['bg-[#f8f7f4]', 'bg-[#ffffff]', 'bg-[#e5e7eb]'];
    $darkPreview = ['bg-[#0f1218]', 'bg-[#161a22]', 'bg-[#2d3340]'];
    $themeDetails = [
        'light' => ['description' => 'Bright background with dark text.', 'previews' => [$lightPreview]],
        'dark' => ['description' => 'Dark background that is easier on the eyes at night.', 'previews' => [$darkPreview]],
        'system' => ['description' => "Follows your device's light or dark setting.", 'previews' => [$lightPreview, $darkPreview]],
    ];
@endphp

@section('settings')
<form method="POST" action="{{ route('settings.display.update') }}"
      class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
    @csrf
    @method('PUT')

    <div class="border-b border-gray-100 p-6">
        <h2 class="text-base font-extrabold text-gray-900">Theme</h2>
        <p class="text-sm text-gray-500">Choose how the teacher portal looks on this browser. Progress records keep the light Child's Record 2 layout.</p>
    </div>

    <fieldset class="p-6">
        <legend class="sr-only">Theme</legend>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach($themes as $value => $label)
                <label class="block cursor-pointer">
                    <input type="radio" name="theme" value="{{ $value }}" class="peer sr-only" @checked($currentTheme === $value) required>
                    <span class="block rounded-2xl border border-gray-200 p-3 transition hover:border-gray-300 peer-checked:border-blue-500 peer-checked:ring-4 peer-checked:ring-blue-500/15 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-500/30">
                        <span class="flex h-24 overflow-hidden rounded-xl border border-gray-200" aria-hidden="true">
                            @foreach($themeDetails[$value]['previews'] as [$page, $surface, $line])
                                <span class="flex flex-1 flex-col p-2.5 {{ $page }}">
                                    <span class="h-full rounded-lg p-2 {{ $surface }}">
                                        <span class="block h-1.5 w-2/3 rounded-full {{ $line }}"></span>
                                        <span class="mt-1.5 block h-1.5 w-1/2 rounded-full {{ $line }}"></span>
                                        <span class="mt-3 block h-3 w-8 rounded-md bg-blue-600"></span>
                                    </span>
                                </span>
                            @endforeach
                        </span>
                        <span class="mt-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-900">{{ $label }}</span>
                            @if($currentTheme === $value)
                                <x-badge color="blue">Current</x-badge>
                            @endif
                        </span>
                        <span class="mt-0.5 block text-xs text-gray-500">{{ $themeDetails[$value]['description'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="flex justify-end border-t border-gray-100 bg-gray-50/60 px-6 py-4">
        <x-button type="submit" icon="save">Save Preference</x-button>
    </div>
</form>
@endsection
