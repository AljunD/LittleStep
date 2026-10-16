@extends('components.app')

@section('title', 'Delete Guardian')

@section('content')
<div class="max-w-2xl mx-auto my-12 px-4">
    <div class="mb-6">
        <a href="{{ route('guardians.index') }}" 
           class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Guardians
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-red-100 shadow-xl overflow-hidden">
        <div class="bg-red-50/70 p-6 border-b border-red-100 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-red-900">Delete Guardian Record</h1>
                <p class="text-xs text-red-600 font-medium mt-0.5">This action cannot be undone and will affect linked records.</p>
            </div>
        </div>

        <div class="p-8 space-y-6">
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 space-y-3 text-sm">
                <div class="flex justify-between py-1 border-b border-gray-200/60">
                    <span class="text-gray-500 font-medium">Guardian Name:</span>
                    <span class="font-bold text-gray-900">{{ trim("{$guardian->first_name} {$guardian->middle_name} {$guardian->last_name}") }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-200/60">
                    <span class="text-gray-500 font-medium">Email Address:</span>
                    <span class="font-bold text-gray-900">{{ $guardian->user->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-gray-200/60">
                    <span class="text-gray-500 font-medium">Relationship:</span>
                    <span class="font-bold text-gray-900">{{ $guardian->relationship_to_child }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500 font-medium">Linked Children:</span>
                    <span class="font-bold text-blue-600">{{ $guardian->children->count() }} {{ Str::plural('child', $guardian->children->count()) }}</span>
                </div>
            </div>

            @if($guardian->children->count() > 0)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start space-x-3 text-amber-800 text-xs">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span><strong>Warning:</strong> Deleting this guardian will unlink or cascade-delete <strong>{{ $guardian->children->count() }} child record(s)</strong> associated with this account.</span>
                </div>
            @endif

            <form action="{{ route('guardians.destroy', $guardian->id) }}" method="POST" class="pt-4 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')

                <a href="{{ route('guardians.index') }}" 
                   class="px-5 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition">
                    Cancel
                </a>
                
                <button type="submit" 
                        class="px-5 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition shadow-sm focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Confirm Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection