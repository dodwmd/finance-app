<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Financial Goals') }}
            </h2>
            <a href="{{ route('goals.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
               dusk="create-goal">
                {{ __('Create New Goal') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Active Goals</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $activeGoals->count() }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Overall Progress</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $summary['overall_progress'] }}%</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Saved</h3>
                        <p class="text-3xl font-bold text-indigo-600">${{ number_format($summary['total_current_amount'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Goals Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-4">Your Financial Goals</h2>
                    
                    @if ($goals->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Date</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($goals as $goal)
                                        <tr>
                                            <td class="py-4 px-4 border-b border-gray-200">
                                                <div class="font-medium text-gray-900">{{ $goal->name }}</div>
                                            </td>
                                            <td class="py-4 px-4 border-b border-gray-200">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ $goalTypeOptions[$goal->type] ?? ucfirst($goal->type) }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 border-b border-gray-200">${{ number_format($goal->target_amount, 2) }}</td>
                                            <td class="py-4 px-4 border-b border-gray-200">
                                                <div class="flex items-center">
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $goal->progress_percentage }}%"></div>
                                                    </div>
                                                    <span class="ml-2 text-sm text-gray-600">{{ $goal->progress_percentage }}%</span>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 border-b border-gray-200">{{ $goal->target_date->format('M d, Y') }}</td>
                                            <td class="py-4 px-4 border-b border-gray-200 text-sm">
                                                <a href="{{ route('goals.show', $goal->id) }}" class="view-goal-button text-indigo-600 hover:text-indigo-900 mr-2" dusk="view-goal-{{ $goal->id }}">View</a>
                                                <a href="{{ route('goals.edit', $goal->id) }}" class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>
                                                <a href="{{ route('goals.progress', $goal->id) }}" class="text-green-600 hover:text-green-900">Progress</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $goals->links() }}
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        You haven't created any financial goals yet. 
                                        <a href="{{ route('goals.create') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                            Create your first goal
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
