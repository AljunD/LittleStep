@extends('components.app')

@section('title', 'Children')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Children</h1>
        <p class="text-sm text-gray-500">Manage and view all registered child profiles.</p>
    </div>

    <div id="table-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Child Full Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Sex</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Linked Guardian</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="childrenTableBody">
                    
                    @forelse($children as $child)
                        <tr class="hover:bg-blue-50 transition-colors group">
                            <td class="px-6 py-5 text-sm text-gray-700">{{ $child->id }}</td>
                            <td class="px-6 py-5 text-sm font-bold text-gray-900">
                                {{ $child->first_name }} {{ $child->middle_name }} {{ $child->last_name }}
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-700">{{ $child->sex }}</td>
                            <td class="px-6 py-5 text-sm text-gray-700">
                                {{ $child->guardian ? $child->guardian->first_name . ' ' . $child->guardian->last_name : 'No Guardian Linked' }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-right">
                                <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('children.show', $child->id) }}" 
                                       class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                        View
                                    </a>
                                    <a href="{{ route('children.edit', $child->id) }}" 
                                       class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-semibold hover:bg-green-200 transition">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-5 text-center text-sm text-gray-500">
                                No children found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
            <p class="text-gray-500">
                Showing {{ $children->firstItem() }} to {{ $children->lastItem() }} of {{ $children->total() }} results
            </p>
            <div class="flex space-x-2">
                @if($children->onFirstPage())
                    <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed">Previous</button>
                @else
                    <a href="{{ $children->previousPageUrl() }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100">Previous</a>
                @endif

                @if($children->hasMorePages())
                    <a href="{{ $children->nextPageUrl() }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100">Next</a>
                @else
                    <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed">Next</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
