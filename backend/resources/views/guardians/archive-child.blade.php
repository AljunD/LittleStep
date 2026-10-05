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
        
        <!-- Header -->
        <div class="px-6 py-6 md:px-8 md:py-8 bg-gray-50 border-b border-gray-200">
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Archive Child Record</h1>
            <p class="text-sm text-gray-500 mt-1">
                Select a child linked to <span class="font-semibold text-gray-800">{{ $guardian->first_name }} {{ $guardian->last_name }}</span> to archive.
            </p>
        </div>

        <!-- Success Message -->
        <div id="success-message" class="hidden p-8 md:p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-green-100 text-green-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✓</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Archive Complete</h3>
                <p class="text-gray-600 mb-8 text-sm">The child record has been successfully archived.</p>
                <a href="{{ route('guardians.archive-child', $guardian->id) }}" 
                   class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95 text-sm">
                    Done
                </a>
            </div>
        </div>

        <!-- Failed Message -->
        <div id="failed-message" class="hidden p-8 md:p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mb-6 bg-red-100 text-red-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">✕</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Archive Failed</h3>
                <p class="text-gray-600 mb-8 text-sm">Something went wrong while archiving the child record. Please try again.</p>
                <a href="{{ route('guardians.archive-child', $guardian->id) }}" 
                   class="inline-block w-full px-8 py-4 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition transform active:scale-95 text-sm">
                    Try Again
                </a>
            </div>
        </div>

        <!-- Child List Table -->
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
                                    <button type="button" 
                                            data-url="{{ route('guardians.unlink-child', ['guardianId' => $guardian->id, 'childId' => $child->id]) }}"
                                            class="archive-btn px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg shadow hover:bg-red-700 transition transform active:scale-95">
                                        Archive Child
                                    </button>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const childList = document.getElementById('child-list');
    const successMessage = document.getElementById('success-message');
    const failedMessage = document.getElementById('failed-message');
    const csrfToken = '{{ csrf_token() }}';

    // Attach event listeners to all Archive buttons
    document.querySelectorAll('.archive-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const endpointUrl = btn.getAttribute('data-url');

            // Confirmation dialog
            const confirmed = confirm("Are you sure you want to archive this child record?");
            if (!confirmed) return;

            btn.disabled = true;
            btn.innerText = "Archiving...";

            try {
                // Execute actual DELETE HTTP request to Laravel route
                const response = await fetch(endpointUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    childList?.classList.add('hidden');
                    successMessage?.classList.remove('hidden');
                    failedMessage?.classList.add('hidden');
                } else {
                    childList?.classList.add('hidden');
                    failedMessage?.classList.remove('hidden');
                    successMessage?.classList.add('hidden');
                }
            } catch (error) {
                console.error("Archive error:", error);
                childList?.classList.add('hidden');
                failedMessage?.classList.remove('hidden');
                successMessage?.classList.add('hidden');
            } finally {
                btn.disabled = false;
                btn.innerText = "Archive Child";
            }

            // Scroll to top for feedback banner visibility
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
});
</script>
@endsection