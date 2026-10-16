@extends('components.app')

@section('title', 'Edit Progress Record')

@section('content')
<div class="max-w-7xl mx-auto my-8 px-4">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Edit ECCD Progress Record</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ $progressRecord->child?->first_name }} {{ $progressRecord->child?->last_name }}
                <span class="text-gray-400">| Record #{{ $progressRecord->id }}</span>
            </p>
        </div>
        <a href="{{ route('progress.show', $progressRecord->id) }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Back to record
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-bold">Please correct the following errors:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('progress.update', $progressRecord->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                <h2 class="font-bold text-gray-900">Assessment dates</h2>
                <p class="mt-1 text-xs text-gray-600">Saved dates are shown below. Leave a date blank if that round has not been administered.</p>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-3">
                @foreach ([1 => '1st', 2 => '2nd', 3 => '3rd'] as $round => $label)
                    <div>
                        <label for="assessment-date-{{ $round }}" class="mb-1 block text-sm font-semibold text-gray-700">
                            {{ $label }} assessment
                        </label>
                        <input
                            id="assessment-date-{{ $round }}"
                            type="date"
                            name="assessment_dates[{{ $round }}]"
                            value="{{ old("assessment_dates.{$round}", $assessmentDates[$round]) } }"
                            min="{{ $progressRecord->child?->date_of_birth }}"
                            max="{{ now()->toDateString() }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        >
                        @error("assessment_dates.{$round}")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Checklist evaluations</h2>
                <p class="mt-1 text-sm text-gray-600">Current saved marks and comments are prefilled for every assessment round.</p>
            </div>

            @foreach ($domainLabels as $domainKey => $domainLabel)
                <details open class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <summary class="cursor-pointer border-b border-gray-200 bg-gray-50 px-5 py-4 font-bold text-gray-900">
                        {{ $domainLabel }}
                        <span class="ml-2 text-xs font-normal text-gray-500">{{ $itemsByDomain->get($domainKey, collect())->count() }} checklist items</span>
                    </summary>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-100 text-left text-xs uppercase tracking-wide text-gray-700">
                                    <th class="w-12 border border-gray-200 px-3 py-3">No.</th>
                                    <th class="w-1/4 border border-gray-200 px-3 py-3">Checklist item</th>
                                    <th class="w-1/4 border border-gray-200 px-3 py-3">Materials / procedure</th>
                                    @foreach ([1 => '1st', 2 => '2nd', 3 => '3rd'] as $round => $label)
                                        <th class="border border-gray-200 px-3 py-3">{{ $label }} assessment</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($itemsByDomain->get($domainKey, collect()) as $item)
                                    @php
                                        $existing = [
                                            1 => $savedResults[1][$item->id] ?? [],
                                            2 => $savedResults[2][$item->id] ?? [],
                                            3 => $savedResults[3][$item->id] ?? [],
                                        ];
                                    @endphp
                                    <tr class="align-top odd:bg-white even:bg-gray-50">
                                        <td class="border border-gray-200 px-3 py-3 text-center font-semibold">{{ $item->item_number ?? $loop->iteration }}</td>
                                        <td class="border border-gray-200 px-3 py-3 font-medium text-gray-800">{{ $item->activity }}</td>
                                        <td class="border border-gray-200 px-3 py-3 text-xs text-gray-600">
                                            {!! nl2br(e($item->materials_and_procedure ?? '')) !!}
                                        </td>
                                        @foreach ([1, 2, 3] as $round)
                                            @php
                                                $answer = old("results.{$item->id}.{$round}", $existing[$round]['present'] ?? '');
                                                $comment = old("comments.{$item->id}.{$round}", $existing[$round]['comments'] ?? '');
                                            @endphp
                                            <td class="border border-gray-200 px-3 py-3">
                                                <label class="mb-2 block text-xs font-semibold text-gray-600" for="result-{{ $item->id }}-{{ $round }}">Response</label>
                                                <select
                                                    id="result-{{ $item->id }}-{{ $round }}"
                                                    name="results[{{ $item->id }}][{{ $round }}]"
                                                    class="w-full rounded border border-gray-300 bg-white px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-200"
                                                >
                                                    <option value="" {{ $answer === '' ? 'selected' : '' }}>No response</option>
                                                    <option value="check" {{ $answer === 'check' ? 'selected' : '' }}>✓ Present</option>
                                                    <option value="hyphen" {{ $answer === 'hyphen' ? 'selected' : '' }}>— Not present</option>
                                                </select>
                                                <label class="mb-1 mt-3 block text-xs font-semibold text-gray-600" for="comment-{{ $item->id }}-{{ $round }}">Comment</label>
                                                <textarea
                                                    id="comment-{{ $item->id }}-{{ $round }}"
                                                    name="comments[{{ $item->id }}][{{ $round }}]"
                                                    rows="3"
                                                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-200"
                                                    placeholder="Optional comment"
                                                >{{ $comment }}</textarea>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                <h2 class="font-bold text-gray-900">Observation notes</h2>
                <p class="mt-1 text-xs text-gray-600">Saved notes and test location are prefilled.</p>
            </div>
            @php($observation = $progressRecord->domainObservation)
            <div class="grid gap-5 p-5 md:grid-cols-2">
                @foreach ([
                    'place_administered' => 'Place where test is administered',
                    'background_observations' => "Child's background",
                    'family_environment_notes' => 'Family environment',
                    'stimulating_activities_notes' => "Parents' stimulating activities",
                    'home_environment_notes' => 'Home environment',
                    'other_notes' => 'Others',
                ] as $field => $label)
                    <div>
                        <label for="observation-{{ $field }}" class="mb-1 block text-sm font-semibold text-gray-700">{{ $label }}</label>
                        <textarea
                            id="observation-{{ $field }}"
                            name="observation[{{ $field }}]"
                            rows="{{ $field === 'place_administered' ? 2 : 4 }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        >{{ old("observation.{$field}", $observation?->{$field} ?? '') }}</textarea>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 pt-5">
            <a href="{{ route('progress.show', $progressRecord->id) }}"
               class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700">
                Save changes
            </button>
        </div>
    </form>
</div>
@endsection