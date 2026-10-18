@extends('components.app')

@section('title', 'Archive Child')

@section('content')
<div class="max-w-5xl mx-auto my-12 px-4 sm:px-6">
    <div class="mb-6">
        <a href="{{ route('guardians.index') }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Guardians
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="px-6 py-6 md:px-8 md:py-8 bg-gray-50 border-b border-gray-200">
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Archive Child Record</h1>
            <p class="text-sm text-gray-500 mt-1">
                Select a child linked to <span class="font-semibold text-gray-800">{{ $guardian->first_name }} {{ $guardian->last_name }}</span> to archive.
            </p>
        </div>

        <div id="child-list" class="p-6 md:p-8">
            <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Sex</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($guardian->children as $child)
                            <tr class="hover:bg-gray-50 transition" id="child-row-{{ $child->id }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    {{ $child->first_name }} {{ $child->middle_name ? $child->middle_name . ' ' : '' }}{{ $child->last_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ ucfirst($child->sex) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-action-menu label="Actions for {{ $child->first_name }} {{ $child->last_name }}">
                                        <form action="{{ route('guardians.unlink-child', ['guardianId' => $guardian->id, 'childId' => $child->id]) }}" method="POST"
                                              data-confirm="Are you sure you want to archive this child record?">
                                            @csrf
                                            @method('DELETE')
                                            <x-action-menu.item icon="archive" variant="danger">Archive Child</x-action-menu.item>
                                        </form>
                                    </x-action-menu>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 italic bg-gray-50">
                                    No active children are currently linked to this guardian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection