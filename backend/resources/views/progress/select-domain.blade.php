@extends('components.app')

@section('title', 'Select Domain')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Select Domain to Evaluate</h1>
        <p class="text-sm text-gray-500 mt-1">
            Choose one of the 7 ECCD domains for 
            <span class="font-semibold">
                {{ $child ? $child->first_name . ' ' . $child->last_name : 'this child' }}
            </span>.
        </p>
    </div>

    {{-- Reminder --}}
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
        <strong>Reminder:</strong>
        You must complete the <strong>1st Evaluation</strong> of all 7 domains before the 2nd Evaluation columns become available.
        Likewise, all 2nd Evaluations must be finished before the 3rd Evaluation columns unlock.
    </div>

    <div class="mb-6">
        <a href="{{ route('progress.index') }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            ← Back to Progress Records
        </a>
    </div>


    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Domain</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">1st Evaluation</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">2nd Evaluation</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">3rd Evaluation</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($domains as $domain)
                        <tr class="hover:bg-blue-50 transition-colors">
                            <td class="px-6 py-5 text-sm font-bold text-gray-900">
                                {{ $domain['name'] }}
                            </td>

                            <td class="px-6 py-5 text-center text-sm">
                                @if($domain['round_status'][1] === 'Completed')
                                    <span class="text-green-600 font-semibold">Completed</span>
                                @elseif($domain['round_status'][1] === 'In Progress')
                                    <span class="text-yellow-600 font-semibold">In Progress</span>
                                @else
                                    <span class="text-gray-400">Pending</span>
                                @endif
                            </td>

                            <td class="px-6 py-5 text-center text-sm">
                                @if($domain['round_status'][2] === 'Completed')
                                    <span class="text-green-600 font-semibold">Completed</span>
                                @elseif($domain['round_status'][2] === 'In Progress')
                                    <span class="text-yellow-600 font-semibold">In Progress</span>
                                @else
                                    <span class="text-gray-400">Pending</span>
                                @endif
                            </td>

                            <td class="px-6 py-5 text-center text-sm">
                                @if($domain['round_status'][3] === 'Completed')
                                    <span class="text-green-600 font-semibold">Completed</span>
                                @elseif($domain['round_status'][3] === 'In Progress')
                                    <span class="text-yellow-600 font-semibold">In Progress</span>
                                @else
                                    <span class="text-gray-400">Pending</span>
                                @endif
                            </td>

                            <td class="px-6 py-5 text-right">
                                <x-button variant="secondary" icon="evaluate" :href="route('progress.create', ['child_id' => $childId, 'domain' => $domain['key']])">Evaluate</x-button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection