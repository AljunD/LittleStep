@extends('components.app')

@section('title', 'Progress Records')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Progress Records</h1>
        <p class="text-sm text-gray-500">View all child progress evaluations.</p>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Child</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Teacher</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Evaluation #</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Evaluation Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    
                    <!-- Example Row -->
                    <tr class="hover:bg-blue-50 transition-colors group">
                        <td class="px-6 py-5 text-sm text-gray-700">1</td>
                        <td class="px-6 py-5 text-sm font-bold text-gray-900">Cj Francisco</td>
                        <td class="px-6 py-5 text-sm text-gray-700">Mr. Juan Dela Cruz</td>
                        <td class="px-6 py-5 text-sm text-gray-700">1</td>
                        <td class="px-6 py-5 text-sm text-gray-700">2026-05-20</td>
                        <td class="px-6 py-5 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-right">
                            <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- View Progress -->
                                <a href="{{ route('progress.show', ['id' => 1]) }}" 
                                   class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                    View
                                </a>
                                <!-- Add Evaluation -->
                                <a href="{{ route('progress.select-domain', ['child_id' => 1]) }}" 
                                   class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold hover:bg-purple-200 transition">
                                    + Add Evaluation
                                </a>
                                <!-- Add Observation -->
                                <a href="{{ route('progress.add-observation', ['child_id' => 1]) }}" 
                                   class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold hover:bg-purple-200 transition">
                                    + Add Observation
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Another Row -->
                    <tr class="hover:bg-blue-50 transition-colors group">
                        <td class="px-6 py-5 text-sm text-gray-700">2</td>
                        <td class="px-6 py-5 text-sm font-bold text-gray-900">Ana Dela Cruz</td>
                        <td class="px-6 py-5 text-sm text-gray-700">Ms. Maria Santos</td>
                        <td class="px-6 py-5 text-sm text-gray-700">2</td>
                        <td class="px-6 py-5 text-sm text-gray-700">2026-05-22</td>
                        <td class="px-6 py-5 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                Completed
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-right">
                            <div class="flex justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- View Progress -->
                                <a href="{{ route('progress.show', ['id' => 2]) }}" 
                                   class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-200 transition">
                                    View
                                </a>
                                <!-- Add Evaluation -->
                                <a href="{{ route('progress.select-domain', ['child_id' => 2]) }}" 
                                   class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold hover:bg-purple-200 transition">
                                    + Add Evaluation
                                </a>
                                <!-- Add Observation -->
                                <a href="{{ route('progress.add-observation', ['child_id' => 2]) }}" 
                                   class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-semibold hover:bg-purple-200 transition">
                                    + Add Observation
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm">
            <p class="text-gray-500">Showing Page 1 of 1</p>
            <div class="flex space-x-2">
                <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed">Previous</button>
                <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 cursor-not-allowed">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection
