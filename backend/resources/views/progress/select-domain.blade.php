@extends('components.app')

@section('title', 'Select Domain')

@section('content')
<div class="space-y-8">
    <h1 class="text-2xl font-bold text-gray-900">Select Domain to Evaluate</h1>
    <p class="text-sm text-gray-500">
        Choose one of the 7 ECCD domains for {{ $child ? $child->first_name . ' ' . $child->last_name : 'this child' }}. Current status is shown for each domain.
    </p>

    <div class="mb-6">
        <a href="{{ route('progress.index') }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Progress Records
        </a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Domain</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Current Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($domains as $domain)
                        <tr class="hover:bg-blue-50 transition-colors">
                            <td class="px-6 py-5 text-sm font-bold text-gray-900">{{ $domain['name'] }}</td>
                            <td class="px-6 py-5 text-sm {{ $domain['status'] === 'Completed' ? 'text-green-600' : ($domain['status'] === 'In Progress' ? 'text-yellow-600' : 'text-gray-600') }}">
                                {{ $domain['status'] }}
                            </td>
                            <td class="px-6 py-5 text-right">
                                <a href="{{ route('progress.create', ['child_id' => $childId ?? request('child_id'), 'domain' => $domain['key']]) }}"
                                   class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                    Evaluate
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection