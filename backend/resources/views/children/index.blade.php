@extends('components.app')

@section('title', 'Children')

@section('content')
<div class="space-y-8">
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
                                <x-action-menu label="Actions for {{ $child->first_name }} {{ $child->last_name }}">
                                    <x-action-menu.item :href="route('children.show', $child->id)" icon="view">View</x-action-menu.item>
                                    <x-action-menu.item :href="route('children.edit', $child->id)" icon="edit">Edit</x-action-menu.item>
                                </x-action-menu>
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
            <x-pagination-buttons :paginator="$children" />
        </div>
    </div>
</div>
@endsection
