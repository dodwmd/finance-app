<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Goal Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('goals.edit', $goal->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    {{ __('Edit Goal') }}
                </a>
                <a href="{{ route('goals.progress', $goal->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    {{ __('View Progress') }}
                </a>
                <a href="{{ route('goals.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Back to Goals') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-3xl font-bold mb-6">{{ $goal->name }}</h1>
                    
                    <!-- Goal Summary Card -->
                    <div class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-xl font-semibold mb-4">Goal Details</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between border-b pb-2">
                                        <span class="text-gray-600">Type:</span>
                                        <span class="font-medium">{{ $goalTypeOptions[$goal->type] ?? ucfirst($goal->type) }}</span>
                                    </div>
                                    <div class="flex justify-between border-b pb-2">
                                        <span class="text-gray-600">Category:</span>
                                        <span class="font-medium">{{ $goal->category?->name ?? 'None' }}</span>
                                    </div>
                                    <div class="flex justify-between border-b pb-2">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="font-medium">
                                            @if($goal->is_completed)
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Completed</span>
                                            @elseif($goal->is_active)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Active</span>
                                            @else
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Inactive</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex justify-between border-b pb-2">
                                        <span class="text-gray-600">Start Date:</span>
                                        <span class="font-medium">{{ $goal->start_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between border-b pb-2">
                                        <span class="text-gray-600">Target Date:</span>
                                        <span class="font-medium">{{ $goal->target_date->format('M d, Y') }}</span>
                                    </div>
                                    @if($goal->description)
                                    <div class="pt-2">
                                        <span class="text-gray-600 block mb-1">Description:</span>
                                        <p class="text-gray-800">{{ $goal->description }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-xl font-semibold mb-4">Progress</h3>
                                
                                <!-- Current vs Target Amount -->
                                <div class="mb-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600">Current Amount:</span>
                                        <span class="text-2xl font-bold text-blue-600">${{ number_format($goal->current_amount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600">Target Amount:</span>
                                        <span class="text-2xl font-bold text-gray-800">${{ number_format($goal->target_amount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600">Remaining:</span>
                                        <span class="text-xl font-semibold text-amber-600">${{ number_format($goal->remaining_amount, 2) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mb-6">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-gray-600">Progress:</span>
                                        <span class="text-gray-600 font-medium">{{ $goal->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $goal->progress_percentage }}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Time Remaining -->
                                @if(!$goal->is_completed)
                                <div class="bg-gray-100 p-4 rounded-lg">
                                    <h4 class="font-medium text-gray-700 mb-2">Time Remaining</h4>
                                    @if($progress['days_remaining'] > 0)
                                        <p class="text-lg"><span class="font-bold">{{ $progress['days_remaining'] }}</span> days left to reach your goal</p>
                                        @if($progress['is_on_track'])
                                            <p class="text-green-600 text-sm mt-1">You're on track to meet your goal!</p>
                                        @else
                                            <p class="text-amber-600 text-sm mt-1">You need to increase contributions to meet your goal on time.</p>
                                        @endif
                                    @elseif($progress['days_remaining'] == 0)
                                        <p class="text-lg font-bold">Today is your target date!</p>
                                    @else
                                        <p class="text-red-600 text-lg">Goal is overdue by {{ abs($progress['days_remaining']) }} days</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap justify-between items-center mt-8">
                        <div>
                            <form id="delete-form" action="{{ route('goals.destroy', $goal->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this goal?')" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Delete Goal') }}
                                </button>
                            </form>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('goals.edit', $goal->id) }}" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Edit Goal') }}
                            </a>
                            <a href="{{ route('goals.progress', $goal->id) }}" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('View Progress') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
