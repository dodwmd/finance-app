<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Goal Progress') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('goals.show', $goal->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    {{ __('View Goal Details') }}
                </a>
                <a href="{{ route('goals.edit', $goal->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    {{ __('Edit Goal') }}
                </a>
                <a href="{{ route('goals.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('All Goals') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Goal Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h1 class="text-3xl font-bold">{{ $goal->name }}</h1>
                    <p class="text-gray-600 mt-2">Progress tracking for your {{ $goalTypeOptions[$goal->type] ?? ucfirst($goal->type) }} goal</p>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Financial Progress -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-gray-800 mb-3">Financial Progress</h3>
                        <div class="flex items-center mb-2">
                            <div class="w-full bg-gray-200 rounded-full h-4 mr-2">
                                <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $progress['amount_percentage'] }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ $progress['amount_percentage'] }}%</span>
                        </div>
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current:</span>
                                <span class="font-medium">${{ number_format($goal->current_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Target:</span>
                                <span class="font-medium">${{ number_format($goal->target_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Remaining:</span>
                                <span class="font-medium">${{ number_format($progress['remaining_amount'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Progress -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-gray-800 mb-3">Time Progress</h3>
                        <div class="flex items-center mb-2">
                            <div class="w-full bg-gray-200 rounded-full h-4 mr-2">
                                <div class="bg-green-500 h-4 rounded-full" style="width: {{ $progress['time_percentage'] }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ $progress['time_percentage'] }}%</span>
                        </div>
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Elapsed:</span>
                                <span class="font-medium">{{ $progress['days_elapsed'] }} days</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total:</span>
                                <span class="font-medium">{{ $progress['days_total'] }} days</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Remaining:</span>
                                <span class="font-medium">{{ $progress['days_remaining'] }} days</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Goal Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg text-gray-800 mb-3">Goal Status</h3>
                        <div class="flex items-center justify-center h-20">
                            @if($goal->is_completed)
                                <div class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Completed
                                    </span>
                                    <p class="text-gray-600 mt-2">Congratulations!</p>
                                </div>
                            @elseif($progress['is_overdue'])
                                <div class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        Overdue
                                    </span>
                                    <p class="text-gray-600 mt-2">Target date has passed</p>
                                </div>
                            @elseif($progress['is_on_track'])
                                <div class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        On Track
                                    </span>
                                    <p class="text-gray-600 mt-2">You're progressing well!</p>
                                </div>
                            @else
                                <div class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1zm1 3a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                                        </svg>
                                        Falling Behind
                                    </span>
                                    <p class="text-gray-600 mt-2">Needs more contributions</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planning & Recommendations -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-xl text-gray-800 mb-4">Recommendations</h3>
                    
                    <div class="rounded-lg border border-gray-200 p-4 mb-4">
                        <h4 class="font-medium text-lg text-gray-800 mb-2">To Stay On Track</h4>
                        
                        @if($goal->is_completed)
                            <p class="text-green-600">Congratulations! You've successfully completed this financial goal.</p>
                        @elseif($progress['days_remaining'] <= 0)
                            <p class="text-red-600">This goal is past its target date. Consider updating the target date or increasing your contributions.</p>
                        @else
                            @php
                                // Calculate recommended monthly contribution
                                $monthsLeft = max(1, ceil($progress['days_remaining'] / 30));
                                $recommendedMonthly = $progress['remaining_amount'] / $monthsLeft;
                                
                                // Calculate recommended weekly contribution
                                $weeksLeft = max(1, ceil($progress['days_remaining'] / 7));
                                $recommendedWeekly = $progress['remaining_amount'] / $weeksLeft;
                            @endphp
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-gray-600 text-sm">Recommended Monthly Contribution:</p>
                                    <p class="text-2xl font-bold text-blue-600">${{ number_format($recommendedMonthly, 2) }}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-gray-600 text-sm">Recommended Weekly Contribution:</p>
                                    <p class="text-2xl font-bold text-blue-600">${{ number_format($recommendedWeekly, 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5 class="font-medium text-gray-700 mb-2">Insights:</h5>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    @if($progress['amount_percentage'] < $progress['time_percentage'])
                                        <li>You're currently behind schedule. Consider increasing your contributions to catch up.</li>
                                    @elseif($progress['amount_percentage'] > $progress['time_percentage'])
                                        <li>You're ahead of schedule! Keep up the good work.</li>
                                    @else
                                        <li>You're right on track with your goal. Keep making consistent contributions.</li>
                                    @endif
                                    
                                    <li>You have {{ $progress['days_remaining'] }} days left to reach your target of ${{ number_format($goal->target_amount, 2) }}.</li>
                                    
                                    @if($recommendedMonthly > 0)
                                        <li>Setting up an automatic monthly transfer of ${{ number_format($recommendedMonthly, 2) }} would help you reach your goal on time.</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-between">
                <a href="{{ route('goals.edit', $goal->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    {{ __('Update Goal Details') }}
                </a>
                <a href="{{ route('goals.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Back to All Goals') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
