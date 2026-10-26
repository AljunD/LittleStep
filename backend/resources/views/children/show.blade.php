@extends('components.app')

@section('title', $child->first_name . ' ' . $child->last_name . ' - Profile')

@section('content')
@php
    $fullName = trim(preg_replace('/\s+/', ' ', "{$child->first_name} {$child->middle_name} {$child->last_name}"));
    $age = $summary['age'];
    $ageLabel = $age['years'].' '.Str::plural('year', $age['years']).', '.$age['months'].' '.Str::plural('month', $age['months']);
    $ordinal = fn (?int $n) => $n === null ? null : $n.(in_array($n % 100, [11, 12, 13], true) ? 'th' : ([1 => 'st', 2 => 'nd', 3 => 'rd'][$n % 10] ?? 'th'));
    $record = $summary['record'];
    $latest = $summary['latest'];
    $overallBands = \App\Services\DashboardInsightsService::OVERALL;
    $domainColor = fn (?string $interpretation) => match ($interpretation) {
        'Suggest significant delay' => 'red',
        'Suggest slight delay' => 'amber',
        'Average development' => 'green',
        'Suggest slightly advanced development' => 'sky',
        'Suggest highly advanced development' => 'violet',
        default => 'gray',
    };
    $valueOrNa = fn ($value) => filled($value) ? $value : 'N/A';
    $archived = $child->trashed();
    $personal = [
        'Full name' => $fullName,
        'Sex' => ucfirst($child->sex),
        'Date of birth' => \Carbon\Carbon::parse($child->date_of_birth)->format('F d, Y'),
        'Age today' => $ageLabel,
        'Handedness' => ucwords(str_replace('_', ' ', $child->handedness ?? 'N/A')),
        'Number of siblings' => $child->number_of_siblings ?? 0,
        'Birth order' => $ordinal($child->birth_order) ? $ordinal($child->birth_order).' child' : 'N/A',
    ];
    $location = [
        'Barangay' => $child->barangay,
        'Municipality / City' => $child->city,
        'Province' => $child->province,
        'Region' => $child->region,
    ];
    $parents = [
        ['title' => 'Father', 'dot' => 'bg-sky-400', 'name' => $child->fathers_name, 'age' => $child->fathers_age, 'occupation' => $child->fathers_occupation, 'education' => $child->fathers_education],
        ['title' => 'Mother', 'dot' => 'bg-pink-400', 'name' => $child->mothers_name, 'age' => $child->mothers_age, 'occupation' => $child->mothers_occupation, 'education' => $child->mothers_education],
    ];
@endphp
<div class="mx-auto max-w-6xl space-y-6">
    <div>
        @if($archived)
            <x-button variant="secondary" icon="back" :href="route('archives.index', ['tab' => 'children'])">Back to Archives</x-button>
        @else
            <x-button variant="secondary" icon="back" :href="route('children.index')">Back to Children</x-button>
        @endif
    </div>

    {{-- Header --}}
    <section class="rounded-2xl border border-gray-200/70 bg-white p-6">
        <div class="flex flex-col items-center gap-6 md:flex-row md:items-start">
            <div class="relative shrink-0">
                <div class="h-32 w-32 overflow-hidden rounded-2xl border border-gray-200 bg-gray-100">
                    <img src="{{ $child->photo_path ? route('children.photo', $child->id) : asset('images/sample-child.jpg') }}"
                         alt="{{ $child->first_name }}'s photo" class="h-full w-full object-cover">
                </div>
            </div>

            <div class="min-w-0 flex-1 text-center md:text-left">
                <div class="flex flex-col items-center gap-2 md:flex-row md:flex-wrap">
                    <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">{{ $fullName }}</h1>
                    <x-badge>{{ ucfirst($child->sex) }}</x-badge>
                    @if($archived)
                        <x-badge color="red" dot>Archived {{ $child->deleted_at->format('M d, Y') }}</x-badge>
                    @endif
                    @if($latest && $latest['interpretation_code'])
                        <x-badge :color="$overallBands[$latest['interpretation_code']]['color']" dot>{{ $overallBands[$latest['interpretation_code']]['label'] }}</x-badge>
                    @endif
                </div>
                <p class="mt-2 text-gray-500">
                    Born {{ \Carbon\Carbon::parse($child->date_of_birth)->format('F d, Y') }} ·
                    <span class="font-semibold text-gray-900">{{ $ageLabel }} old</span>
                </p>
                @if($child->guardian)
                    <p class="mt-1 text-sm text-gray-500">
                        Guardian:
                        <a href="{{ route('guardians.show', $child->guardian->id) }}" class="font-semibold text-gray-800 hover:underline">
                            {{ $child->guardian->first_name }} {{ $child->guardian->last_name }}
                        </a>
                        @if($child->guardian->relationship_to_child)
                            ({{ ucfirst($child->guardian->relationship_to_child) }})
                        @endif
                    </p>
                @endif
            </div>

            @unless($archived)
            <div class="flex shrink-0 items-center gap-2">
                @if($record)
                    <x-button :href="route('progress.show', $record->id)" icon="view">View Progress</x-button>
                @else
                    <x-button :href="route('progress.select-domain', ['child_id' => $child->id])" icon="evaluate">Start Evaluation</x-button>
                @endif
                <x-action-menu label="Child actions">
                    <x-action-menu.item :href="route('children.edit', $child->id)" icon="edit">Edit Profile</x-action-menu.item>
                    <x-action-menu.item :href="route('progress.select-domain', ['child_id' => $child->id])" icon="evaluate">Evaluate</x-action-menu.item>
                    <x-action-menu.item :href="route('progress.add-observation', ['child_id' => $child->id])" icon="observation">Add Observation</x-action-menu.item>
                    <form action="{{ route('children.destroy', $child->id) }}" method="POST" data-confirm="Are you sure you want to archive this record?">
                        @csrf
                        @method('DELETE')
                        <x-action-menu.item icon="archive" variant="danger">Archive Record</x-action-menu.item>
                    </form>
                </x-action-menu>
            </div>
            @endunless
        </div>
    </section>

    {{-- Quick facts --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" data-child-quick-facts>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200/70 bg-white p-5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-lg text-amber-600" aria-hidden="true"><i class="fas fa-cake-candles"></i></span>
            <div>
                <p class="text-sm font-semibold text-gray-500">Age Today</p>
                <p class="text-xl font-extrabold text-gray-900">{{ $age['years'] }}y {{ $age['months'] }}m</p>
                <p class="text-xs text-gray-400">{{ $age['band'] === 'unknown' ? 'Outside the ECCD age bands' : 'ECCD age band '.str_replace('-', '–', $age['band']) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200/70 bg-white p-5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-lg text-sky-600" aria-hidden="true"><i class="fas fa-clipboard-check"></i></span>
            <div>
                <p class="text-sm font-semibold text-gray-500">Evaluations</p>
                <p class="text-xl font-extrabold text-gray-900">{{ $summary['evaluations_done'] }} of 3</p>
                <p class="text-xs text-gray-400">{{ $record ? 'Dates recorded' : 'No progress record yet' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200/70 bg-white p-5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-lg text-emerald-600" aria-hidden="true"><i class="fas fa-chart-line"></i></span>
            <div>
                <p class="text-sm font-semibold text-gray-500">Latest Standard Score</p>
                <p class="text-xl font-extrabold text-gray-900">{{ $latest['standard_score'] ?? '—' }}</p>
                <p class="text-xs text-gray-400">{{ $latest ? $latest['label'] : 'Not scored yet' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200/70 bg-white p-5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-lg text-violet-600" aria-hidden="true"><i class="fas fa-puzzle-piece"></i></span>
            <div>
                <p class="text-sm font-semibold text-gray-500">Skills to Practice</p>
                <p class="text-xl font-extrabold text-gray-900">{{ $summary['skills_to_practice'] }}</p>
                <p class="text-xs text-gray-400">Items marked — in the latest evaluation</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- ECCD progress --}}
            <section class="space-y-5 rounded-2xl border border-gray-200/70 bg-white p-6" data-child-progress>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">ECCD Progress</h2>
                        <p class="text-sm text-gray-500">Scores from the ECCD checklist, computed the same way as the progress record.</p>
                    </div>
                    @if($record)
                        <x-status-badge :status="$record->status" />
                    @endif
                </div>

                @if(! $record)
                    <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-gray-200 py-10 text-center">
                        <i class="fas fa-clipboard-list text-2xl text-gray-300" aria-hidden="true"></i>
                        <p class="text-sm text-gray-500">This child has not been evaluated yet.</p>
                        @unless($archived)
                            <x-button :href="route('progress.select-domain', ['child_id' => $child->id])" icon="evaluate">Start Evaluation</x-button>
                        @endunless
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @foreach($summary['evaluations'] as $evaluation)
                            <div @class(['rounded-xl border p-4', 'border-gray-200 bg-gray-50' => $evaluation['date'], 'border-dashed border-gray-200' => ! $evaluation['date']])>
                                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">{{ $evaluation['label'] }}</p>
                                @if($evaluation['date'])
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $evaluation['date']->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">Age at test: {{ $evaluation['age'] ?? '—' }}</p>
                                    <div class="mt-3 flex items-end justify-between gap-2">
                                        <div>
                                            <p class="text-[11px] text-gray-400">Standard score</p>
                                            <p class="text-xl font-extrabold text-gray-900">{{ $evaluation['standard_score'] ?? '—' }}</p>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $evaluation['scored_domains'] }}/7 domains</span>
                                    </div>
                                    @if($evaluation['interpretation_code'])
                                        <x-badge :color="$overallBands[$evaluation['interpretation_code']]['color']" class="mt-2">{{ $overallBands[$evaluation['interpretation_code']]['label'] }}</x-badge>
                                    @endif
                                @else
                                    <p class="mt-1 text-sm text-gray-400">Not yet tested</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($latest)
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Domain Results · {{ $latest['label'] }}</h3>
                            <ul class="divide-y divide-gray-100">
                                @foreach($latest['domains'] as $domain)
                                    <li class="flex flex-col gap-2 py-2.5 sm:flex-row sm:items-center sm:gap-4">
                                        <span class="w-44 shrink-0 text-sm font-semibold text-gray-800">{{ $domain['label'] }}</span>
                                        <div class="flex flex-1 items-center gap-3">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100">
                                                <div @class(['h-full rounded-full', 'bg-amber-400' => $domain['suggests_delay'], 'bg-emerald-400' => ! $domain['suggests_delay']])
                                                     @style(['width: '.round(($domain['scaled'] ?? 0) / 19 * 100).'%'])></div>
                                            </div>
                                            <span class="w-10 text-right text-sm font-bold text-gray-900">{{ $domain['scaled'] ?? '—' }}</span>
                                        </div>
                                        <x-badge :color="$domainColor($domain['interpretation'])" class="sm:w-56 sm:justify-center">{{ $domain['interpretation'] ?? 'Not scored' }}</x-badge>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs text-gray-400">Scaled scores range from 1 to 19. {{ $latest['overall'] }}.</p>
                        </div>
                    @endif
                @endif
            </section>

            {{-- Personal & location --}}
            <section class="rounded-2xl border border-gray-200/70 bg-white p-6">
                <h2 class="mb-4 text-base font-extrabold text-gray-900">Personal Details</h2>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach($personal as $label => $value)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <h2 class="mb-4 mt-8 text-base font-extrabold text-gray-900">Address</h2>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach($location as $label => $value)
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $valueOrNa($value) }}</dd>
                        </div>
                    @endforeach
                </dl>

                <h2 class="mb-3 mt-8 text-base font-extrabold text-gray-900">Education</h2>
                <div class="rounded-xl bg-gray-50 p-4 text-sm">
                    @if($child->is_studying)
                        <p class="font-medium text-gray-800">Currently enrolled at <span class="font-bold">{{ $valueOrNa($child->school_name) }}</span></p>
                    @else
                        <p class="italic text-gray-500">Not currently enrolled in any school.</p>
                    @endif
                </div>
            </section>

            {{-- Observation notes --}}
            <section class="rounded-2xl border border-gray-200/70 bg-white p-6" data-child-observations>
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">Observation Notes</h2>
                        <p class="text-sm text-gray-500">Notes about the child and their home environment.</p>
                    </div>
                    @unless($archived)
                        <x-button variant="secondary" :href="route('progress.add-observation', ['child_id' => $child->id])" icon="observation">
                            {{ $summary['observations'] ? 'Edit Notes' : 'Add Notes' }}
                        </x-button>
                    @endunless
                </div>
                @forelse($summary['observations'] as $note)
                    <div class="border-t border-gray-100 py-3 first:border-t-0 first:pt-0">
                        <p class="text-xs font-semibold uppercase text-gray-400">{{ $note['label'] }}</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $note['text'] }}</p>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-gray-200 py-6 text-center text-sm text-gray-400">No observation notes yet.</p>
                @endforelse
            </section>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-gray-200/70 bg-white p-5">
                <h3 class="mb-4 flex items-center text-sm font-bold text-gray-900">
                    <span class="mr-2 h-2 w-2 rounded-full bg-emerald-400" aria-hidden="true"></span> Linked Guardian
                </h3>
                @if($child->guardian)
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Name</dt>
                            <dd class="font-semibold text-gray-800">{{ $child->guardian->first_name }} {{ $child->guardian->last_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Relationship</dt>
                            <dd class="text-gray-800">{{ $valueOrNa(ucfirst((string) $child->guardian->relationship_to_child)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Email</dt>
                            <dd class="break-all text-gray-800">{{ $child->guardian->user->email ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Contact Number</dt>
                            <dd class="text-gray-800">{{ $valueOrNa($child->guardian->contact_number) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">App Access</dt>
                            <dd>
                                @if($child->guardian->user?->email_verified_at)
                                    <x-badge color="green" dot>Email verified</x-badge>
                                @else
                                    <x-badge color="amber" dot>Email not verified</x-badge>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <x-button variant="secondary" :href="route('guardians.show', $child->guardian->id)" icon="view" class="mt-4 w-full">View Guardian</x-button>
                @else
                    <p class="text-sm italic text-gray-500">No guardian linked.</p>
                @endif
            </div>

            @foreach($parents as $parent)
                <div class="rounded-2xl border border-gray-200/70 bg-white p-5">
                    <h3 class="mb-4 flex items-center text-sm font-bold text-gray-900">
                        <span class="mr-2 h-2 w-2 rounded-full {{ $parent['dot'] }}" aria-hidden="true"></span> {{ $parent['title'] }}
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Name</dt>
                            <dd class="font-semibold text-gray-800">{{ $valueOrNa($parent['name']) }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <dt class="text-[11px] font-bold uppercase text-gray-400">Age</dt>
                                <dd class="text-gray-800">{{ $valueOrNa($parent['age']) }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-bold uppercase text-gray-400">Occupation</dt>
                                <dd class="text-gray-800">{{ $valueOrNa($parent['occupation']) }}</dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Education</dt>
                            <dd class="text-gray-800">{{ $valueOrNa($parent['education']) }}</dd>
                        </div>
                    </dl>
                </div>
            @endforeach

            <div class="rounded-2xl border border-gray-200/70 bg-white p-5" data-child-record-details>
                <h3 class="mb-4 flex items-center text-sm font-bold text-gray-900">
                    <span class="mr-2 h-2 w-2 rounded-full bg-gray-400" aria-hidden="true"></span> Record Details
                </h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Child ID</dt>
                        <dd class="font-semibold text-gray-800">#CHD-{{ str_pad($child->id, 5, '0', STR_PAD_LEFT) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Profile Created</dt>
                        <dd class="text-gray-800">{{ $child->created_at?->format('M d, Y') ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase text-gray-400">Last Updated</dt>
                        <dd class="text-gray-800">{{ $child->updated_at?->diffForHumans() ?? 'N/A' }}</dd>
                    </div>
                    @if($record)
                        <div>
                            <dt class="text-[11px] font-bold uppercase text-gray-400">Progress Record Updated</dt>
                            <dd class="text-gray-800">{{ $record->updated_at?->diffForHumans() ?? 'N/A' }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </aside>
    </div>
</div>
@endsection
