@extends('components.app')

@section('title', 'Guardian Profile')

@section('content')
<div class="max-w-6xl mx-auto my-10 px-4">
    <div class="mb-6">
        <a href="{{ route('guardians.index') }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Guardians
        </a>
    </div>

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('guardians.index') }}" class="hover:text-blue-600 transition">Guardians</a></li>
                    <li>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path>
                        </svg>
                    </li>
                    <li class="font-medium text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Guardian Profile</h1>
        </div>
        
        <x-action-menu label="Guardian actions">
            <x-action-menu.item :href="route('guardians.edit', $guardian->id)" icon="edit">Edit Details</x-action-menu.item>
            <x-action-menu.item :href="route('guardians.create-child', $guardian->id)" icon="link">Link Child</x-action-menu.item>
        </x-action-menu>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-bold text-gray-800">Personal Information</h2>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Full Name</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                {{ trim("{$guardian->first_name} {$guardian->middle_name} {$guardian->last_name}") }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Relationship to Child</label>
                            <p class="mt-1 text-lg font-semibold text-blue-600">{{ $guardian->relationship_to_child }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Sex</label>
                            <p class="mt-1 text-gray-800 font-medium">{{ $guardian->sex }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Contact Number</label>
                            <p class="mt-1 text-gray-800 font-medium">{{ $guardian->contact_number }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Home Address</label>
                            <p class="mt-1 text-gray-800 leading-relaxed">
                                {{ $guardian->address ?? 'No address recorded.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800">Linked Children</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        {{ $guardian->children->count() }} {{ Str::plural('Record', $guardian->children->count()) }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($guardian->children as $child)
                        <div class="p-6 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-4">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-500 font-bold text-xl overflow-hidden shrink-0">
                                    @if($child->photo_path)
                                        <img src="{{ route('children.photo', $child->id) }}" alt="{{ $child->first_name }}" class="w-full h-full object-cover">
                                    @else
                                        <img src="{{ asset('images/sample-child.jpg') }}" alt="{{ $child->first_name }}">
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        {{ trim("{$child->first_name} {$child->middle_name} {$child->last_name}") }}
                                    </h4>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">
                                        {{ \Carbon\Carbon::parse($child->date_of_birth)->age }} yrs old • {{ $child->sex }}
                                        @if($child->school_name)
                                            • {{ $child->school_name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('children.show', $child->id) }}" class="p-2 text-gray-400 hover:text-blue-600 transition" title="View Child Profile">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 text-sm">
                            <p class="italic">No children currently linked to this guardian.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-gray-400 mb-6">Account Status</h2>

                <div class="space-y-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Email Address</p>
                        @if($guardian->user)
                            <p class="font-medium text-gray-900 break-all">{{ $guardian->user->email }}</p>
                            @if($guardian->user->email_verified_at)
                                <x-badge color="green" dot class="mt-2">Verified Account</x-badge>
                            @else
                                <x-badge color="amber" dot class="mt-2">Unverified Account</x-badge>
                            @endif
                        @else
                            <p class="font-medium text-gray-500 italic">No linked user account</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">System Role</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $guardian->user ? ucfirst($guardian->user->role) : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Guardian ID</p>
                            <p class="text-sm font-semibold text-gray-900">#GRD-{{ str_pad($guardian->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-gray-500">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div class="text-xs">
                            <p class="font-bold text-gray-400 uppercase">Created</p>
                            <p class="text-gray-900">{{ $guardian->created_at ? $guardian->created_at->format('M d, Y • h:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-gray-500">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581
                                     m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <div class="text-xs">
                            <p class="font-bold text-gray-400 uppercase">Last Updated</p>
                            <p class="text-gray-900">{{ $guardian->updated_at ? $guardian->updated_at->format('M d, Y • h:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection