@extends('components.app')

@section('title', 'Progress Record - View')

@section('content')
<div class="max-w-6xl mx-auto my-10 px-4">
    <div class="mb-6">
        <a href="{{ route('progress.index') }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Progress Records
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="px-8 py-6 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Progress Record #{{ $progressRecord?->id ?? $id ?? '—' }}
                </h1>
                <p class="text-sm text-gray-500 font-medium">Early Childhood Care and Development (ECCD) Checklist</p>
            </div>
            <div class="flex items-start gap-3">
                <x-progress-status class="sm:text-right" :status="$roundProgress['status']" :completed="$roundProgress['completed']" :round="$roundProgress['round']" />
                @if($progressRecord)
                    <x-action-menu label="Progress record actions">
                        <x-action-menu.item :href="route('progress.edit', $progressRecord->id)" icon="edit">Edit Record</x-action-menu.item>
                        <x-action-menu.item :href="route('progress.download-pdf', $progressRecord->id)" icon="download">Download PDF Report</x-action-menu.item>
                    </x-action-menu>
                @endif
            </div>
        </div>

        {{-- Child / Teacher / Date info --}}
        <div class="p-8 grid grid-cols-1 md:grid-cols-4 gap-8 text-sm text-gray-700">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">Child</p>
                <p class="mt-1 text-base font-semibold text-gray-900 underline decoration-dotted decoration-gray-300">
                    {{ $child?->first_name ?? '—' }} {{ $child?->last_name ?? '' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">Teacher/Examiner</p>
                <p class="mt-1 text-base font-semibold text-gray-900 underline decoration-dotted decoration-gray-300">
                    {{ $progressRecord?->teacher?->first_name ?? '—' }}
                    {{ $progressRecord?->teacher?->last_name ?? '' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">Assessment Date</p>
                <p class="mt-1 text-base font-medium text-gray-900">
                    {{ $roundProgress['date'] ? \Carbon\Carbon::parse($roundProgress['date'])->format('F d, Y') : 'Not yet tested' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">Assessment No.</p>
                <p class="mt-1 text-base font-medium text-gray-900">
                    {{ [1 => '1st', 2 => '2nd', 3 => '3rd'][$roundProgress['round']] }} of 3
                </p>
            </div>
        </div>

        <hr class="border-gray-100">

        {{-- Computation of the Child's Age --}}
        <div class="mt-16 px-4 sm:px-8">
            <div class="text-center mb-6">
                <p class="text-red-600 font-black italic text-lg uppercase mb-2">It is recommended that the Checklist be administered to the child once a year.</p>
                <h2 class="text-4xl font-black text-[#001f5b] italic uppercase tracking-tight">Computation of the Child's Age</h2>
                @if($progressRecord)
                    <x-edit-section-link class="mt-3" :href="route('progress.edit', $progressRecord->id).'#assessment-dates'" label="assessment dates" />
                @endif
                <div class="max-w-3xl mx-auto mt-4 text-sm text-gray-700 leading-relaxed font-medium px-4">
                    <p>After verifying the dates, compute the child's age by subtracting the date he was born from the date the test was administered. Each month is composed of 30 days. Do not round off the months or years. Write the examiner's name each time the test is administered.</p>
                </div>
            </div>

            <div class="max-w-5xl mx-auto border-[3px] border-[#001f5b] overflow-hidden bg-white shadow-sm overflow-x-auto">
                <table class="w-full border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-[#7a9ab5] text-white uppercase text-sm font-black italic tracking-widest">
                            <th class="py-3 px-4 border-r-2 border-[#001f5b] w-1/4"></th>
                            <th class="py-3 border-r-2 border-[#001f5b]">Year</th>
                            <th class="py-3 border-r-2 border-[#001f5b]">Month</th>
                            <th class="py-3 border-r-2 border-[#001f5b]">Day</th>
                            <th class="py-3 w-1/3">Examiner's Name</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-[#001f5b]">
                        @php
                            $birth = $child?->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth) : null;
                            $examiner = trim(($progressRecord?->teacher?->first_name ?? '') . ' ' . ($progressRecord?->teacher?->last_name ?? ''));
                            $assessments = [];
                            $assessmentLabels = [1 => '1st', 2 => '2nd', 3 => '3rd'];
                            $assessmentColors = [1 => '#ff0000', 2 => '#ff9900', 3 => '#ffff00'];

                            foreach ([1, 2, 3] as $round) {
                                $date = !empty($assessmentDates[$round])
                                    ? \Carbon\Carbon::parse($assessmentDates[$round])
                                    : null;
                                $age = $ageByRound[$round] ?? null;
                                $assessments[] = [
                                    'label' => $assessmentLabels[$round],
                                    'color' => $assessmentColors[$round],
                                    'date' => $date?->format('Y') ?? '',
                                    'month' => $date?->format('m') ?? '',
                                    'day' => $date?->format('d') ?? '',
                                    'birth_y' => $birth?->format('Y') ?? '',
                                    'birth_m' => $birth?->format('m') ?? '',
                                    'birth_d' => $birth?->format('d') ?? '',
                                    'age_y' => $age['years'] ?? '',
                                    'age_m' => $age['months'] ?? '',
                                    'age_d' => $age['days'] ?? '',
                                    'examiner' => $examiner,
                                ];
                            }
                        @endphp
                        @foreach($assessments as $asmt)
                        <tr class="h-32">
                            <td class="relative border-r-2 border-[#001f5b] p-0 overflow-hidden">
                                <div class="absolute inset-0 flex">
                                    {{-- Official colours; the dark outline keeps the white label readable on the 3rd assessment's yellow. --}}
                                    <div class="w-16 flex flex-col items-center justify-center text-white font-black italic leading-none shrink-0" style="background-color: {{ $asmt['color'] }}; text-shadow: 0 0 2px rgba(0, 31, 91, .55), 0 1px 1px rgba(0, 31, 91, .45);">
                                        <span class="text-3xl">{{ $asmt['label'] }}</span>
                                        <span class="text-[8px] uppercase tracking-tighter mt-1">assessment</span>
                                    </div>
                                    <div class="flex-1 flex flex-col justify-between text-[11px] font-black uppercase p-2 text-[#001f5b] italic">
                                        <div class="border-b border-[#001f5b]/20 pb-1">Date Tested</div>
                                        <div class="border-b border-[#001f5b]/20 py-1">Child's Date of Birth</div>
                                        <div class="pt-1">Child's Age</div>
                                    </div>
                                </div>
                            </td>
                            <td class="border-r-2 border-[#001f5b] relative">
                                <div class="absolute inset-0 flex flex-col divide-y divide-[#001f5b]/30 text-center font-bold text-lg">
                                    <div class="flex-1 flex items-center justify-center">{{ $asmt['date'] }}</div>
                                    <div class="flex-1 flex items-center justify-center bg-gray-50 italic text-gray-400">{{ $asmt['birth_y'] }}</div>
                                    <div class="flex-1 flex items-center justify-center text-[#001f5b]">{{ $asmt['age_y'] }}</div>
                                </div>
                            </td>
                            <td class="border-r-2 border-[#001f5b] relative">
                                <div class="absolute inset-0 flex flex-col divide-y divide-[#001f5b]/30 text-center font-bold text-lg">
                                    <div class="flex-1 flex items-center justify-center">{{ $asmt['month'] }}</div>
                                    <div class="flex-1 flex items-center justify-center bg-gray-50 italic text-gray-400">{{ $asmt['birth_m'] }}</div>
                                    <div class="flex-1 flex items-center justify-center text-[#001f5b]">{{ $asmt['age_m'] }}</div>
                                </div>
                            </td>
                            <td class="border-r-2 border-[#001f5b] relative">
                                <div class="absolute inset-0 flex flex-col divide-y divide-[#001f5b]/30 text-center font-bold text-lg">
                                    <div class="flex-1 flex items-center justify-center">{{ $asmt['day'] }}</div>
                                    <div class="flex-1 flex items-center justify-center bg-gray-50 italic text-gray-400">{{ $asmt['birth_d'] }}</div>
                                    <div class="flex-1 flex items-center justify-center text-[#001f5b]">{{ $asmt['age_d'] }}</div>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <div class="h-full flex items-center justify-center border-b-2 border-dotted border-[#001f5b] font-bold text-[#001f5b] italic">
                                    {{ $asmt['examiner'] }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-8 space-y-16">
            @forelse($preparedDomains ?? [] as $key => $data)
                <div>
                    <div class="mb-6 flex flex-col items-center gap-2">
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight text-center">
                            {{ $data['name'] }} Domain
                        </h2>
                        <x-edit-section-link :href="route('progress.edit', $progressRecord->id).'#domain-'.$key" :label="$data['name'].' checklist'" />
                    </div>
                    @include('progress.partials.domain-table', [
                        'domainName' => $data['name'],
                        'items'      => $data['items']
                    ])
                </div>
            @empty
                <p class="text-center text-gray-500 py-16">No domain evaluation data available yet.</p>
            @endforelse
        </div>

        {{-- Notes Section --}}
        <div class="px-6 py-8 sm:p-10 space-y-8 bg-white border-t border-gray-100">
            @if($progressRecord)
                <div class="flex justify-end">
                    <x-edit-section-link :href="route('progress.edit', $progressRecord->id).'#observation-notes'" label="observation notes" />
                </div>
            @endif
            <div class="grid grid-cols-1 gap-3 max-w-3xl font-mono text-[11px] sm:text-xs">
                <div class="flex items-baseline gap-2">
                    <span class="whitespace-nowrap text-gray-700">Name of examiner :</span>
                    <span class="flex-1 border-b border-dashed border-gray-400 px-2 text-black">
                        {{ $progressRecord?->teacher?->first_name ?? '' }}
                        {{ $progressRecord?->teacher?->last_name ?? '' }}
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="whitespace-nowrap text-gray-700">Date administered :</span>
                    <span class="flex-1 border-b border-dashed border-gray-400 px-2 text-black">
                        {{ $progressRecord?->evaluation_date
                            ? \Carbon\Carbon::parse($progressRecord->evaluation_date)->format('F d, Y')
                            : '—' }}
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="whitespace-nowrap text-gray-700">Place where test is administered :</span>
                    <span class="flex-1 border-b border-dashed border-gray-400 px-2 text-black">
                        {{ $progressRecord?->domainObservation?->place_administered ?: '—' }}
                    </span>
                </div>
                <p class="text-[10px] text-gray-500">
                    Child: {{ trim(($child?->first_name ?? '') . ' ' . ($child?->last_name ?? '')) ?: '—' }}
                </p>
            </div>
            <div>
                @php
                    $obs = $progressRecord?->domainObservation;
                    $sections = [
                        "Child's background (behavior, health, etc.)" => $obs?->background_observations ?? '—',
                        "Family environment (family health, relationships, or living conditions)" => $obs?->family_environment_notes ?? '—',
                        "Parents' stimulating activities for the child" => $obs?->stimulating_activities_notes ?? '—',
                        "Home environment (facilities, household items, and interactions)" => $obs?->home_environment_notes ?? '—',
                        'Others' => $obs?->other_notes ?? '—',
                    ];
                @endphp
                <div class="mb-7 space-y-1 text-[11px] leading-relaxed text-gray-800">
                    <p>To the examiner:</p>
                    <p>Please use the spaces below to record additional information.</p>
                    <p>Write notes and observations in the sections that follow.</p>
                </div>
                @foreach($sections as $title => $content)
                <div class="mb-8">
                    <h3 class="mb-2 text-[11px] sm:text-xs font-medium text-gray-800">{{ $title }}</h3>
                    <div class="px-1 text-[11px] sm:text-xs text-black leading-5 border-b border-gray-200 pb-1">
                        {{ $content }}
                    </div>
                    <div aria-hidden="true">
                        @for ($line = 0; $line < 4; $line++)
                            <div class="h-[22px] border-b border-gray-200"></div>
                        @endfor
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Raw / Scaled Scores + Charts --}}
        <div class="p-2 sm:p-4 md:p-8 space-y-10">
            <p class="text-[11px] text-gray-600 mb-4 leading-relaxed">
                Transfer the raw score for each domain in the table below. Using the <strong>Scaled Score Equivalent of Raw Scores Table</strong>, convert the raw scores to Scaled Scores appropriate to the age of the child. To arrive at the Sum of Scaled Scores, add the Scaled Scores across all domains. To derive the Standard Score, refer to the <strong>Standard Score Equivalent of Sums of Scaled Scores Table</strong>. Write the Child's age on each evaluation.
            </p>

            <div class="w-full border-[3px] border-[#001f5b] overflow-hidden shadow-md">
                <table class="w-full table-fixed border-collapse text-[10px] sm:text-xs">
                    <thead>
                        <tr>
                            <th rowspan="3" class="w-1/4 bg-[#001f5b] text-white text-base sm:text-4xl font-black italic leading-tight px-0.5 sm:px-3 py-5 sm:py-8 border-r-4 border-[#001f5b] break-words">Domain</th>
                            <th colspan="6" class="bg-white text-[#001f5b] text-2xl sm:text-4xl font-black italic py-2 border-b-2 border-[#001f5b]">Age</th>
                        </tr>
                        <tr class="bg-white text-[8px] sm:text-[11px] text-gray-800">
                            <th colspan="2" class="border-r-2 border-[#001f5b] border-b py-1 px-0.5 sm:py-2 sm:px-2 text-center">
                                <div class="flex flex-wrap justify-center gap-x-1 mb-1">
                                    <span>1<sup>st</sup> Evaluation</span>
                                    <span>Date: <span class="underline">
                                        {{ !empty($assessmentDates[1])
                                            ? \Carbon\Carbon::parse($assessmentDates[1])->format('m/d/Y')
                                            : '________' }}
                                    </span></span>
                                </div>
                                <div class="border-t border-gray-300 pt-1 mx-auto">
                                    <span class="text-gray-600">Child's Age: <span class="underline">
                                        {{ $ageByRound[1]['label'] ?? '________' }}
                                    </span></span>
                                </div>
                            </th>
                            <th colspan="2" class="border-r-2 border-[#001f5b] border-b py-1 px-0.5 sm:py-2 sm:px-2 text-center">
                                <div class="flex flex-wrap justify-center gap-x-1 mb-1">
                                    <span>2<sup>nd</sup> Evaluation</span>
                                    <span>Date: <span class="underline">
                                        {{ !empty($assessmentDates[2])
                                            ? \Carbon\Carbon::parse($assessmentDates[2])->format('m/d/Y')
                                            : '________' }}
                                    </span></span>
                                </div>
                                <div class="border-t border-gray-300 pt-1 mx-auto">
                                    <span>Child's Age: <span class="underline">{{ $ageByRound[2]['label'] ?? '________' }}</span></span>
                                </div>
                            </th>
                            <th colspan="2" class="border-b py-1 px-0.5 sm:py-2 sm:px-2 text-center">
                                <div class="flex flex-wrap justify-center gap-x-1 mb-1">
                                    <span>3<sup>rd</sup> Evaluation</span>
                                    <span>Date: <span class="underline">
                                        {{ !empty($assessmentDates[3])
                                            ? \Carbon\Carbon::parse($assessmentDates[3])->format('m/d/Y')
                                            : '________' }}
                                    </span></span>
                                </div>
                                <div class="border-t border-gray-300 pt-1 mx-auto">
                                    <span>Child's Age: <span class="underline">{{ $ageByRound[3]['label'] ?? '________' }}</span></span>
                                </div>
                            </th>
                        </tr>
                        <tr class="bg-[#ff0000] text-white text-[7px] sm:text-[10px] font-bold uppercase tracking-tighter">
                            <th class="px-0.5 py-1 border-r border-white/30">Raw</th>
                            <th class="px-0.5 py-1 border-r-2 border-[#001f5b]">Scaled</th>
                            <th class="px-0.5 py-1 border-r border-white/30">Raw</th>
                            <th class="px-0.5 py-1 border-r-2 border-[#001f5b]">Scaled</th>
                            <th class="px-0.5 py-1 border-r border-white/30">Raw</th>
                            <th class="px-0.5 py-1">Scaled</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#001f5b]/20">
                        @php
                            $domainDisplay = [
                                'gross_motor'         => 'Gross Motor',
                                'fine_motor'          => 'Fine Motor',
                                'self_help'           => 'Self-Help',
                                'receptive_language'  => 'Receptive Language',
                                'expressive_language' => 'Expressive Language',
                                'cognitive'           => 'Cognitive',
                                'social_emotional'    => 'Social- Emotional',
                            ];
                        @endphp
                        @foreach($domainDisplay as $key => $label)
                        <tr class="h-10">
                            <td class="px-1 sm:px-4 py-1 font-bold text-[9px] sm:text-xs text-gray-800 border-r-4 border-[#001f5b] break-words">{{ $label }}</td>
                            @foreach ([1, 2, 3] as $round)
                            <td class="border-r border-gray-200 bg-white text-center text-[10px] sm:text-xs">
                                {{ $rawScoresByRound[$round][$key] ?? '—' }}
                            </td>
                            <td class="{{ $round < 3 ? 'border-r-2 border-[#001f5b]' : '' }} bg-white text-center text-[10px] sm:text-xs font-bold">
                                {{ $computedByRound[$round]['scaled_scores'][$key] ?? '—' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach

                        <tr class="h-10">
                            <td class="px-1 sm:px-4 py-1 font-bold text-[9px] sm:text-xs text-[#e91e63] border-r-4 border-[#001f5b] break-words">Sum of Scaled Scores</td>
                            <td class="bg-[#879fb1] border-r border-white/20"></td>
                            <td class="border-r-2 border-[#001f5b] bg-white text-center font-bold text-[#001f5b]">
                                {{ $computedByRound[1]['sum_scaled_score'] ?? '—' }}
                            </td>
                            <td class="bg-[#879fb1] border-r border-white/20"></td>
                            <td class="border-r-2 border-[#001f5b] bg-white text-center font-bold text-[#001f5b]">
                                {{ $computedByRound[2]['sum_scaled_score'] ?? '—' }}
                            </td>
                            <td class="bg-[#879fb1] border-r border-white/20"></td>
                            <td class="bg-white text-center font-bold text-[#001f5b]">
                                {{ $computedByRound[3]['sum_scaled_score'] ?? '—' }}
                            </td>
                        </tr>
                        <tr class="h-10">
                            <td class="px-1 sm:px-4 py-1 font-bold text-[9px] sm:text-xs text-[#e91e63] border-r-4 border-[#001f5b] break-words">Standard Score</td>
                            <td class="bg-[#879fb1] border-r border-white/20"></td>
                            <td class="border-r-2 border-[#001f5b] bg-white text-center font-bold text-[#001f5b]">
                                {{ $computedByRound[1]['standard_score'] ?? '—' }}
                            </td>
                            <td class="bg-[#879fb1] border-r border-white/20"></td>
                            <td class="border-r-2 border-[#001f5b] bg-white text-center font-bold text-[#001f5b]">
                                {{ $computedByRound[2]['standard_score'] ?? '—' }}
                            </td>
                            <td class="bg-[#879fb1] border-r border-white/20"></td>
                            <td class="bg-white text-center font-bold text-[#001f5b]">
                                {{ $computedByRound[3]['standard_score'] ?? '—' }}
                            </td>
                        </tr>
                        <tr class="h-16">
                            <td class="px-1 sm:px-4 py-1 font-bold text-[9px] sm:text-xs text-[#e91e63] border-r-4 border-[#001f5b] break-words">Interpretation</td>
                            <td colspan="2" class="border-r-2 border-[#001f5b] bg-white text-center text-[8px] sm:text-[11px] font-medium p-1 sm:p-2 italic">
                                {{ $computed['interpretation']['overall'] ?? '—' }}
                            </td>
                            <td colspan="2" class="border-r-2 border-[#001f5b] bg-white text-center text-[8px] sm:text-[11px] font-medium p-1 sm:p-2 italic">
                                {{ $computedByRound[2]['interpretation']['overall'] ?? '—' }}
                            </td>
                            <td colspan="2" class="bg-white text-center text-[8px] sm:text-[11px] font-medium p-1 sm:p-2 italic">
                                {{ $computedByRound[3]['interpretation']['overall'] ?? '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @include('progress.partials.expert-explanation')

            <div class="mt-16">
                <h2 class="text-4xl font-black text-[#001f5b] text-center italic mb-2">Scaled Scores</h2>
                <p class="text-xs text-gray-700 mb-6 text-center">Mark an <strong>x</strong> on the dot corresponding to the Scaled Score for each domain and connect the <strong>x's</strong>. Write the child's age on each evaluation.</p>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 border-4 border-[#001f5b]">
                    @for ($eval = 1; $eval <= 3; $eval++)
                    <div class="{{ $eval < 3 ? 'lg:border-r-4 lg:border-[#001f5b]' : '' }} p-4 bg-white">
                        <div class="flex items-center justify-center space-x-2 mb-4">
                            <span class="text-[#001f5b] font-bold text-sm">Child's Age:</span>
                            <span class="border-b border-[#001f5b] w-48 text-center text-sm font-semibold">
                                {{ $ageByRound[$eval]['label'] ?? '________' }}
                            </span>
                        </div>

                        <div class="bg-[#001f5b] text-white text-[10px] font-bold py-1 text-center uppercase tracking-widest mb-0.5">Domain</div>

                        <div class="flex border border-[#001f5b]">
                            <div class="flex shrink-0">
                                <div class="flex flex-col w-10 border-r border-[#001f5b] text-[11px] font-bold text-center leading-tight">
                                    <div class="h-32 border-b border-[#001f5b]"></div>
                                    <div class="h-[calc(6*1.5rem)] flex items-center justify-center bg-[#ffff99] [writing-mode:vertical-lr] rotate-180 border-b border-gray-300">Suggests advanced development</div>
                                    <div class="h-[calc(7*1.5rem)] flex items-center justify-center bg-[#ffff99] [writing-mode:vertical-lr] rotate-180 border-b border-gray-300 relative">
                                        <div class="absolute inset-0 flex items-center justify-center">Average development</div>
                                    </div>
                                    <div class="h-[calc(6*1.5rem)] flex items-center justify-center bg-[#fdfeca] [writing-mode:vertical-lr] rotate-180">Re-test after 3- 6 months</div>
                                </div>

                                <div class="flex flex-col w-10 border-r border-[#001f5b]">
                                    <div class="h-32 bg-red-600 text-white [writing-mode:vertical-lr] rotate-180 text-[10px] font-bold flex items-center justify-center border-b border-[#001f5b]">SCALED SCORE</div>
                                    @for ($i = 19; $i >= 1; $i--)
                                    <div class="h-6 flex items-center justify-center border-b border-gray-300 text-xs font-bold 
                                        {{ $i == 10 ? 'bg-[#8fa9bc] text-black' : ($i >= 7 ? 'bg-[#ffff99]' : 'bg-[#fdfeca]') }}">
                                        {{ $i }}
                                    </div>
                                    @endfor
                                </div>
                            </div>

                            <div class="flex-1 grid grid-cols-7">
                                @foreach($domainDisplay as $key => $domain)
                                <div class="flex flex-col border-r border-gray-300 last:border-r-0">
                                    <div class="h-32 border-b border-[#001f5b] relative bg-white">
                                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 [writing-mode:vertical-lr] rotate-180 text-[8px] font-bold uppercase text-gray-800 whitespace-nowrap">{{ $domain }}</div>
                                    </div>

                                    @for ($i = 19; $i >= 1; $i--)
                                    <div class="h-6 border-b border-gray-300 last:border-b-0 flex items-center justify-center
                                        {{ $i == 10 ? 'bg-[#8fa9bc]' : ($i >= 7 ? 'bg-[#ffff99]' : 'bg-[#fdfeca]') }}">
                                        
                                        @if(isset($computedByRound[$eval]['scaled_scores'][$key]) && $computedByRound[$eval]['scaled_scores'][$key] == $i)
                                            <span class="font-black text-[#001f5b] text-sm">x</span>
                                        @else
                                            <div class="w-1 h-1 bg-black rounded-full opacity-80"></div>
                                        @endif
                                    </div>
                                    @endfor
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <div class="mt-16">
                <h2 class="text-4xl font-black text-[#001f5b] text-center italic mb-2 uppercase">Standard Scores</h2>
                <p class="text-xs text-gray-700 mb-6 text-center italic">
                    Mark an <strong>x</strong> on the corresponding standard score for each test administration and connect the <strong>x's</strong>. Write the date for each test administration.
                </p>
                
                <div class="max-w-5xl mx-auto border-4 border-[#001f5b] overflow-x-auto">
                    <div class="min-w-[900px]">
                        <div class="grid grid-cols-[180px_1fr] bg-white border-b-4 border-[#001f5b]">
                            <div class="border-r-2 border-[#001f5b] flex items-center justify-center py-2">
                                <span class="text-2xl font-black italic text-[#001f5b] tracking-widest">AGES</span>
                            </div>
                            <div class="grid grid-cols-3 text-white text-sm font-black uppercase italic tracking-tight">
                                <div class="bg-[#1e1e9c] py-4 text-center border-r border-white/20">3 years & 1 month</div>
                                <div class="bg-[#9c1e9c] py-4 text-center border-r border-white/20">4 years</div>
                                <div class="bg-[#ff007f] py-4 text-center">5 years</div>
                            </div>
                        </div>

                        <div class="flex">
                            <div class="w-[180px] flex shrink-0 border-r-2 border-[#001f5b]">
                                <div class="w-16 flex flex-col border-r border-gray-300 font-black text-[9px] uppercase text-center leading-tight bg-white">
                                    <div class="h-[174px] flex items-center justify-center bg-white [writing-mode:vertical-lr] rotate-180 px-1 border-b border-gray-400">Suggests advanced development</div>
                                    <div class="h-[217.5px] flex items-center justify-center bg-white [writing-mode:vertical-lr] rotate-180 px-1 border-b border-gray-400">Average development</div>
                                    <div class="h-[217.5px] flex items-center justify-center bg-white [writing-mode:vertical-lr] rotate-180 px-1">Re-test after 3 to 6 months</div>
                                </div>
                                
                                <div class="relative h-[609px] flex-1 text-right">
                                    <div class="absolute inset-x-0 top-0 h-[174px] bg-[#fffd66]"></div>
                                    <div class="absolute inset-x-0 top-[174px] h-[217.5px] bg-[#ffff99]"></div>
                                    <div class="absolute inset-x-0 top-[391.5px] h-[217.5px] bg-[#ffffcc]"></div>
                                    @for ($s = 160; $s >= 20; $s -= 10)
                                    @php
                                        $scoreLabelPosition = match ($s) {
                                            160 => 'translate-y-0',
                                            20 => '-translate-y-full',
                                            default => '-translate-y-1/2',
                                        };
                                    @endphp
                                    <div class="absolute inset-x-0 flex {{ $scoreLabelPosition }} items-center justify-end pr-3 text-sm font-black text-[#001f5b]"
                                         style="top: {{ ((160 - $s) / 140) * 100 }}%">
                                        {{ $s }} <span class="ml-2 w-3 h-[2px] bg-[#001f5b]"></span>
                                    </div>
                                    @endfor
                                </div>
                            </div>

                            <div class="flex-1 grid grid-cols-3 h-[609px]">
                                @php
                                    $xTops = [];
                                    foreach ([1, 2, 3] as $round) {
                                        $stdScore = $computedByRound[$round]['standard_score'] ?? null;
                                        $xTops[$round] = $stdScore !== null && $stdScore >= 20 && $stdScore <= 160
                                            ? ((160 - $stdScore) / 140) * 609
                                            : null;
                                    }
                                @endphp

                                <div class="relative border-r border-[#001f5b]/30 overflow-hidden">
                                    <div class="absolute top-0 w-full h-[174px] bg-[#fffd66]"></div>
                                    <div class="absolute top-[174px] w-full h-[217.5px] bg-[#ffff99]"></div>
                                    <div class="absolute top-[391.5px] w-full h-[217.5px] bg-[#ffffcc]"></div>
                                    <div class="absolute inset-x-0 h-px bg-[#001f5b]/30" style="top: {{ ((160 - 100) / 140) * 100 }}%"></div>
                                    <div class="absolute inset-0 flex justify-center pointer-events-none">
                                        <div class="w-px bg-[#001f5b]/30 h-full relative">
                                            @for ($val = 160; $val >= 20; $val--)
                                                @php $distanceFromTop = ((160 - $val) / 140) * 609; @endphp
                                                <div class="absolute h-px bg-[#001f5b]/30 -translate-y-1/2
                                                    {{ $val % 10 == 0 ? 'w-6 -left-3' : 'w-3 -left-1.5' }}"
                                                    style="top: {{ $distanceFromTop }}px">
                                                </div>
                                            @endfor
                                        </div>
                                    </div>

                                    @if($xTops[1] !== null)
                                        <div class="absolute left-1/2 -translate-x-1/2 -translate-y-1/2 z-10"
                                             style="top: {{ $xTops[1] }}px">
                                            <span class="font-black text-[#001f5b] text-xl leading-none">x</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="relative border-r border-[#001f5b]/30 overflow-hidden">
                                    <div class="absolute top-0 w-full h-[174px] bg-[#fffd66]"></div>
                                    <div class="absolute top-[174px] w-full h-[217.5px] bg-[#ffff99]"></div>
                                    <div class="absolute top-[391.5px] w-full h-[217.5px] bg-[#ffffcc]"></div>
                                    <div class="absolute inset-x-0 h-px bg-[#001f5b]/30" style="top: {{ ((160 - 100) / 140) * 100 }}%"></div>
                                    <div class="absolute inset-0 flex justify-center pointer-events-none">
                                        <div class="w-px bg-[#001f5b]/30 h-full relative">
                                            @for ($val = 160; $val >= 20; $val--)
                                                @php $distanceFromTop = ((160 - $val) / 140) * 609; @endphp
                                                <div class="absolute h-px bg-[#001f5b]/30 -translate-y-1/2
                                                    {{ $val % 10 == 0 ? 'w-6 -left-3' : 'w-3 -left-1.5' }}"
                                                    style="top: {{ $distanceFromTop }}px">
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                    @if($xTops[2] !== null)
                                        <div class="absolute left-1/2 -translate-x-1/2 -translate-y-1/2 z-10"
                                             style="top: {{ $xTops[2] }}px">
                                            <span class="font-black text-[#001f5b] text-xl leading-none">x</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="relative overflow-hidden">
                                    <div class="absolute top-0 w-full h-[174px] bg-[#fffd66]"></div>
                                    <div class="absolute top-[174px] w-full h-[217.5px] bg-[#ffff99]"></div>
                                    <div class="absolute top-[391.5px] w-full h-[217.5px] bg-[#ffffcc]"></div>
                                    <div class="absolute inset-x-0 h-px bg-[#001f5b]/30" style="top: {{ ((160 - 100) / 140) * 100 }}%"></div>
                                    <div class="absolute inset-0 flex justify-center pointer-events-none">
                                        <div class="w-px bg-[#001f5b]/30 h-full relative">
                                            @for ($val = 160; $val >= 20; $val--)
                                                @php $distanceFromTop = ((160 - $val) / 140) * 609; @endphp
                                                <div class="absolute h-px bg-[#001f5b]/30 -translate-y-1/2
                                                    {{ $val % 10 == 0 ? 'w-6 -left-3' : 'w-3 -left-1.5' }}"
                                                    style="top: {{ $distanceFromTop }}px">
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                    @if($xTops[3] !== null)
                                        <div class="absolute left-1/2 -translate-x-1/2 -translate-y-1/2 z-10"
                                             style="top: {{ $xTops[3] }}px">
                                            <span class="font-black text-[#001f5b] text-xl leading-none">x</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-[180px_1fr] border-t-4 border-[#001f5b] bg-white">
                            <div class="border-r-2 border-[#001f5b] flex items-center px-4 py-3">
                                <span class="text-xs font-black text-[#001f5b] uppercase">Date Tested</span>
                            </div>
                            <div class="grid grid-cols-3 font-bold text-sm">
                                <div class="p-4 border-r border-[#001f5b] text-center underline decoration-dotted">
                                    {{ !empty($assessmentDates[1])
                                        ? \Carbon\Carbon::parse($assessmentDates[1])->format('m/d/Y')
                                        : 'MM / DD / YYYY' }}
                                </div>
                                <div class="p-4 border-r border-[#001f5b] text-center">
                                    {{ !empty($assessmentDates[2])
                                        ? \Carbon\Carbon::parse($assessmentDates[2])->format('m/d/Y')
                                        : 'MM / DD / YYYY' }}
                                </div>
                                <div class="p-4 text-center">
                                    {{ !empty($assessmentDates[3])
                                        ? \Carbon\Carbon::parse($assessmentDates[3])->format('m/d/Y')
                                        : 'MM / DD / YYYY' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style nonce="{{ request()->attributes->get('cspNonce') }}">
    .border-navy-900 { border-color: #001f5b; }
    .bg-navy-900 { background-color: #001f5b; }
    
    @media print {
        @page { margin: 0; }
        body { padding: 2cm; }
        .no-print { display: none; }
    }
</style>
@endsection