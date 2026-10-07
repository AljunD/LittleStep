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

    {{-- Session Banners --}}
    @if(session('success'))
    <div id="success-alert" class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl shadow-sm flex items-center justify-between animate-fade-in">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-sm">✓</div>
            <div>
                <p class="font-semibold text-sm">Success</p>
                <p class="text-xs text-green-700">{{ session('success') }}</p>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 text-lg font-bold px-2">&times;</button>
    </div>
    @endif

    @if(session('error') || session('failed'))
    <div id="failed-alert" class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm flex items-center justify-between animate-fade-in">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold text-sm">✕</div>
            <div>
                <p class="font-semibold text-sm">Error</p>
                <p class="text-xs text-red-700">{{ session('error') ?? session('failed') }}</p>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 text-lg font-bold px-2">&times;</button>
    </div>
    @endif

    <!-- Search bar & Count counter -->
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

    {{-- Full-page Archive Success Panel --}}
    <div id="success-message" class="hidden p-12 text-center bg-white rounded-2xl border border-gray-100 shadow-sm animate-fade-in">
        <div class="max-w-md mx-auto">
            <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Archive Complete</h3>
            <p class="text-gray-600 mb-8 text-sm">The guardian and all linked children have been successfully archived.</p>
            <a href="{{ route('guardians.index') }}" 
               class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95 text-sm">
                Return to Guardians
            </a>
        </div>
    </div>

    {{-- Full-page Archive Failed Panel --}}
    <div id="failed-message" class="hidden p-12 text-center bg-white rounded-2xl border border-gray-100 shadow-sm animate-fade-in">
        <div class="max-w-md mx-auto">
            <div class="mb-6 bg-red-100 text-red-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✕</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Archive Failed</h3>
            <p class="text-gray-600 mb-8 text-sm">Something went wrong while archiving the guardian. Please try again.</p>
            <button type="button" onclick="resetIndexLayout()" 
               class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95 text-sm">
                Go Back and Try Again
            </button>
        </div>
    </div>

    <!-- Table Container -->
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

                                <button type="button" 
                                        data-id="{{ $guardian->id }}"
                                        data-name="{{ $guardian->first_name }} {{$guardian->last_name }}"
                                        class="archive-trigger-btn px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-lg text-xs font-semibold hover:bg-yellow-200 transition">
                                    Archive
                                </button>

                                <form id="archive-form-{{ $guardian->id }}" action="{{ route('guardians.destroy', $guardian->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Live Client-Side Table Filter Search
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

    // 2. Archive Handler – AJAX
    document.getElementById('guardiansTableBody')?.addEventListener('click', function(e) {
        const btn = e.target.closest('.archive-trigger-btn');
        if (btn) {
            const guardianId = btn.getAttribute('data-id');
            const guardianName = btn.getAttribute('data-name');

            const confirmed = confirm(`Are you sure you want to archive ${guardianName} and all linked children records?`);
            if (!confirmed) return;

            const targetForm = document.getElementById(`archive-form-${guardianId}`);
            if (!targetForm) {
                alert("Error: Archive processing form could not be located.");
                return;
            }

            if (btn.disabled) return;
            btn.disabled = true;
            const originalText = btn.innerText;
            btn.innerText = "Archiving...";

            const formData = new FormData(targetForm);

            fetch(targetForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (!response.ok) {
                    document.getElementById('index-header')?.classList.add('hidden');
                    document.getElementById('search-container')?.classList.add('hidden');
                    document.getElementById('table-container')?.classList.add('hidden');
                    document.getElementById('success-message')?.classList.add('hidden');
                    document.getElementById('failed-message')?.classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                // Success – hide list UI and show success panel
                document.getElementById('index-header')?.classList.add('hidden');
                document.getElementById('search-container')?.classList.add('hidden');
                document.getElementById('table-container')?.classList.add('hidden');
                document.getElementById('failed-message')?.classList.add('hidden');
                document.getElementById('success-message')?.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(err => {
                console.error(err);
                document.getElementById('index-header')?.classList.add('hidden');
                document.getElementById('search-container')?.classList.add('hidden');
                document.getElementById('table-container')?.classList.add('hidden');
                document.getElementById('success-message')?.classList.add('hidden');
                document.getElementById('failed-message')?.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = originalText;
            });
        }
    });
});

// Restore list UI
function resetIndexLayout() {
    document.getElementById('index-header')?.classList.remove('hidden');
    document.getElementById('search-container')?.classList.remove('hidden');
    document.getElementById('table-container')?.classList.remove('hidden');
    document.getElementById('failed-message')?.classList.add('hidden');
    document.getElementById('success-message')?.classList.add('hidden');

    document.querySelectorAll('.archive-trigger-btn').forEach(btn => {
        btn.disabled = false;
        if (btn.innerText === 'Archiving...') {
            btn.innerText = 'Archive';
        }
    });
}
</script>
@endsection