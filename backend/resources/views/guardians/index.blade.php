@extends('components.app')

@section('title', 'Guardians')

@section('content')
<div class="space-y-8">
    <div id="index-header" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Guardians</h1>
            <p class="text-sm text-gray-500">Manage and view all registered child guardians.</p>
        </div>
        <a href="{{ route('guardians.create') }}" 
           class="px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow hover:bg-blue-700 transition transform active:scale-95 text-center">
            + Add Guardian
        </a>
    </div>

    {{-- Success / error banners come from <x-flash-messages /> in the layout. --}}

    <div id="search-container" class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
            <input type="text" id="searchInput" placeholder="Search guardians by name, email, or details..." 
                   class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition outline-none">
        </div>
        <div class="text-xs font-semibold text-gray-500 self-center">
            Showing <span id="resultCount" class="text-blue-600 font-bold">{{ $guardians->count() }}</span> filtered records
        </div>
    </div>

    <div id="table-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Guardian</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Address</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Relationship</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Linked Children</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="guardiansTableBody">
                    @forelse($guardians as $guardian)
                    <tr class="hover:bg-blue-50/40 transition-colors group guardian-row">
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">{{ $guardian->first_name }} {{$guardian->last_name }}</div>
                            @if($guardian->email)
                                <div class="text-xs text-gray-400">{{ $guardian->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">
                            {{ $guardian->contact_number }}
                        </td>
                        <td class="px-6 py-5 text-sm text-gray-600 max-w-xs truncate" title="{{ $guardian->address ?? 'No Address Listed' }}">
                            {{ $guardian->address ?? 'No Address Listed' }}
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                {{ $guardian->relationship_to_child }}
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="text-sm text-gray-900 space-y-0.5">
                                @if(!isset($guardian->children) || $guardian->children->isEmpty())
                                    <span class="text-xs text-gray-400 italic">No linked children</span>
                                @else
                                    @foreach($guardian->children as $child)
                                        <div>{{ $child->first_name }} {{$child->last_name }}</div>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-right">
                            <div class="flex justify-end items-center gap-1.5 flex-wrap">
                                <a href="{{ route('guardians.show', $guardian->id) }}" 
                                   class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-200 transition">
                                    View
                                </a>
                                <a href="{{ route('guardians.edit', $guardian->id) }}" 
                                   class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-200 transition">
                                    Edit
                                </a>
                                <a href="{{ route('guardians.create-child', $guardian->id) }}" 
                                   class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold hover:bg-purple-200 transition">
                                    Link Child
                                </a>
                                
                                <a href="{{ route('guardians.archive-child', $guardian->id) }}" 
                                   class="px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg text-xs font-semibold hover:bg-orange-200 transition">
                                    Unlink Child
                                </a>

                                <form action="{{ route('guardians.destroy', $guardian->id) }}" method="POST" class="inline-block"
                                      data-confirm="Are you sure you want to archive {{ $guardian->first_name }} {{ $guardian->last_name }} and all linked children records?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-lg text-xs font-semibold hover:bg-yellow-200 transition">
                                        Archive
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="empty-state-row">
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400 italic bg-gray-50">
                            No registered guardians found in the system.
                        </td>
                    </tr>
                    @endforelse
                    <tr id="no-search-results-row" class="hidden">
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400 italic bg-gray-50">
                            No matching guardians found for your search query.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @if($guardians->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
            {{ $guardians->links() }}
        </div>
        @endif
    </div>
</div>

<script nonce="{{ request()->attributes->get('cspNonce') }}">
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const resultCount = document.getElementById('resultCount');
    const rows = document.querySelectorAll('#guardiansTableBody tr.guardian-row');
    const noResultsRow = document.getElementById('no-search-results-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            let visibleCount = 0;

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(term)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (resultCount) resultCount.textContent = visibleCount;

            if (noResultsRow) {
                if (visibleCount === 0 && rows.length > 0) {
                    noResultsRow.classList.remove('hidden');
                } else {
                    noResultsRow.classList.add('hidden');
                }
            }
        });
    }
});
</script>
@endsection