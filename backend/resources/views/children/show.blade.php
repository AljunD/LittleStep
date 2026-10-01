@extends('components.app')

@section('title', 'Child Profile')

@section('content')
<div class="max-w-5xl mx-auto my-10 px-4">
    <div class="mb-6">
        <a href="{{ route('children.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Children List
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="relative px-8 py-10 bg-gradient-to-r from-slate-50 to-gray-100 border-b border-gray-200">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <!-- Photo -->
                <div class="relative">
                    <div class="w-40 h-40 rounded-2xl overflow-hidden border-4 border-white shadow-lg bg-gray-200">
                        <img src="{{ asset('images/sample-child.jpg') }}" alt="Child Photo" class="w-full h-full object-cover">
                    </div>
                    <span class="absolute -bottom-2 -right-2 bg-green-500 border-4 border-white w-6 h-6 rounded-full" title="Active Record"></span>
                </div>

                <!-- Basic Info -->
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2">
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Cj Francisco</h1>
                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 w-fit mx-auto md:mx-0">
                            Male
                        </span>
                    </div>
                    <p class="text-gray-500 text-lg mb-4">
                        Born June 03, 2022 • <span class="text-gray-900 font-semibold">2 Years Old</span>
                    </p>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap justify-center md:justify-start gap-3">
                        <a href="{{ route('children.edit', $child->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            Edit Profile
                        </a>
                        <a href="{{ route('archives.index') }}" class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-100 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-100 transition">
                            Unlink Record
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="p-8 grid grid-cols-1 lg:grid-cols-3 gap-12">
            <div class="lg:col-span-2 space-y-10">
                <!-- Personal & Location -->
                <section>
                    <h3 class="text-sm font-bold text-blue-600 uppercase tracking-wider mb-5 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Personal & Location Details
                    </h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Barangay</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">Socorro</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Municipality/City</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">Quezon City</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Province/Region</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">Metro Manila, NCR</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Handedness</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">Right-handed</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Number of Siblings</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">3</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Birth Order</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">1</dd>
                        </div>
                    </dl>
                </section>

                <hr class="border-gray-100">

                <!-- Education -->
                <section>
                    <h3 class="text-sm font-bold text-blue-600 uppercase tracking-wider mb-5 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        </svg>
                        Education Status
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <p class="text-sm text-gray-600 italic">Not currently enrolled in any educational institution.</p>
                    </div>
                </section>
            </div>

            <!-- Parents -->
            <div class="space-y-6">
                <!-- Linked Guardian -->
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span> Linked Guardian
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Name</p>
                            <p class="text-sm font-semibold text-gray-800">Maria Santos</p>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Email</p>
                                <p class="text-sm text-gray-800">maria.santos@example.com</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Contact Number</p>
                            <p class="text-sm text-gray-800">09123456789</p>
                        </div>
                    </div>
                </div>
                <!-- Father -->
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-2 h-2 bg-blue-400 rounded-full mr-2"></span> Father
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Name</p>
                            <p class="text-sm font-semibold text-gray-800">Aljun Bequillos Dalman</p>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Age</p>
                                <p class="text-sm text-gray-800">25</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Occupation</p>
                                <p class="text-sm text-gray-800 italic">None</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Education</p>
                            <p class="text-sm text-gray-800">College Graduate</p>
                        </div>
                    </div>
                </div>

                <!-- Mother -->
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-2 h-2 bg-pink-400 rounded-full mr-2"></span> Mother
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Name</p>
                            <p class="text-sm font-semibold text-gray-800">Kahit Sino Nalang</p>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Age</p>
                                <p class="text-sm text-gray-800">26</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Occupation</p>
                                <p class="text-sm text-gray-800 italic">None</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Education</p>
                            <p class="text-sm text-gray-800">College Graduate</p>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
    </div> 
</div>
@endsection
