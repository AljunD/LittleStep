@extends('settings.layout')

@section('title', 'Help & About')

@php
    $guides = [
        [
            'icon' => 'fa-people-roof',
            'title' => 'Registering guardians and children',
            'steps' => [
                'Open Guardians and choose Add Guardian to register a guardian together with their first child.',
                'The guardian receives an email to verify their account before they can sign in to the LittleStep mobile app.',
                'Use the ⋮ menu on a guardian to link another child, unlink a child, or resend the verification email.',
            ],
        ],
        [
            'icon' => 'fa-seedling',
            'title' => 'Recording an evaluation',
            'steps' => [
                'Open Progress and use the ⋮ menu on a child to choose Add Evaluation, then pick a domain.',
                'Mark each checklist item ✓ when the child shows the skill and — when they do not yet. The live preview shows the scores as you go.',
                'Each child can be evaluated in up to 3 rounds. Use Add Observation for notes about the child and their home environment.',
            ],
        ],
        [
            'icon' => 'fa-calculator',
            'title' => 'How scores are computed',
            'steps' => [
                "The child's age at test is counted from the birth date to the test date, with every month counted as 30 days. Child's Record 2 covers ages 3 years 1 month to 5 years 11 months.",
                'The raw score of a domain is the number of ✓ items. Each raw score is converted to a scaled score from 1 to 19 using the official table for the child\'s age band (7–13 is average).',
                'The 7 scaled scores are added up and converted to a standard score with its interpretation. The "How Was This Computed?" section of a progress record shows every step for that child.',
            ],
        ],
        [
            'icon' => 'fa-file-pdf',
            'title' => 'Reports',
            'steps' => [
                "Open a child's progress record and download the PDF to get the official Child's Record 2 with the child's results filled in.",
            ],
        ],
        [
            'icon' => 'fa-box-archive',
            'title' => 'Archiving and restoring',
            'steps' => [
                'Archiving hides a guardian or child without deleting their records.',
                'Open Archives to find archived guardians and children and restore them from the ⋮ menu.',
            ],
        ],
        [
            'icon' => 'fa-shield-halved',
            'title' => 'Your account and privacy',
            'steps' => [
                'You only see the guardians and children you have evaluated, and child photos are never public.',
                'Change your password on the Profile tab. Doing so signs out your other devices and sends you a confirmation email.',
                'System Logs lists the actions recorded from your account.',
            ],
        ],
    ];
@endphp

@section('settings')
<div class="space-y-6">
    <section class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
        <div class="flex items-center gap-4 border-b border-gray-100 p-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white" aria-hidden="true">
                <i class="fas fa-shoe-prints -rotate-90"></i>
            </span>
            <div>
                <h2 class="text-base font-extrabold text-gray-900">About LittleStep</h2>
                <p class="text-sm text-gray-500">Teacher Portal</p>
            </div>
        </div>
        <div class="space-y-4 p-6 text-sm leading-relaxed text-gray-600">
            <p>
                LittleStep helps daycare teachers track young children's development with the ECCD (Early Childhood Care and Development) Checklist.
                Teachers record evaluations and observations here, and guardians follow their child's progress in the LittleStep mobile app.
            </p>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">ECCD Domains</p>
                <ul class="mt-2 flex flex-wrap gap-2">
                    @foreach(\App\Services\EccdExpertSystem::DOMAIN_LABELS as $domainLabel)
                        <li><x-badge color="blue">{{ $domainLabel }}</x-badge></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white">
        <div class="border-b border-gray-100 p-6">
            <h2 class="text-base font-extrabold text-gray-900">Help Guides</h2>
            <p class="text-sm text-gray-500">Select a topic to see how it works.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($guides as $guide)
                <details class="group" @if($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center gap-3 px-6 py-4 transition hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-sm text-gray-500" aria-hidden="true">
                            <i class="fas {{ $guide['icon'] }}"></i>
                        </span>
                        <span class="flex-1 text-sm font-bold text-gray-900">{{ $guide['title'] }}</span>
                        <x-icon name="next" class="text-gray-400 transition group-open:rotate-90" />
                    </summary>
                    <ol class="list-decimal space-y-2 px-6 pb-5 pl-[4.25rem] text-sm leading-relaxed text-gray-600">
                        @foreach($guide['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                </details>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200/70 bg-white p-6">
        <h2 class="text-base font-extrabold text-gray-900">Need more help?</h2>
        <p class="mt-1 text-sm text-gray-500">
            For account problems or questions about your center's records, contact your daycare center's administrator.
        </p>
    </section>
</div>
@endsection
