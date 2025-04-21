<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Budget Details') }}
            </h2>
            <div>
                <a href="{{ route('budgets.progress', $budget) }}" dusk="view-progress" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                    {{ __('View Progress') }}
                </a>
                <a href="{{ route('budgets.edit', $budget) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                    {{ __('Edit Budget') }}
                </a>
                <a href="{{ route('budgets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to Budgets') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Budget Progress Card -->
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Budget Progress</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $budget->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $budget->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-gray-700 text-sm">Amount Spent: </span>
                                <span class="font-semibold {{ $budgetProgress['is_exceeded'] ? 'text-red-600' : 'text-gray-800' }}">
                                    ${{ number_format($budgetProgress['spent'], 2) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-700 text-sm">Budget: </span>
                                <span class="font-semibold text-gray-800">${{ number_format($budget->amount, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-700 text-sm">Remaining: </span>
                                <span class="font-semibold {{ $budgetProgress['remaining'] <= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    ${{ number_format($budgetProgress['remaining'], 2) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-1">
                            <div class="h-3 rounded-full {{ $budgetProgress['is_exceeded'] ? 'bg-red-600' : ($budgetProgress['percentage'] >= 80 ? 'bg-yellow-400' : 'bg-blue-600') }}" 
                                 style="width: {{ min($budgetProgress['percentage'], 100) }}%"></div>
                        </div>
                        
                        <div class="flex justify-between text-xs text-gray-600 mb-4">
                            <span>0%</span>
                            <span>{{ $budgetProgress['percentage'] }}% Used</span>
                            <span>100%</span>
                        </div>
                        
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <div>
                                <span class="font-medium">Period:</span> {{ ucfirst($budget->period) }}
                            </div>
                            <div>
                                <span class="font-medium">Date Range:</span> 
                                {{ \Carbon\Carbon::parse($budgetProgress['start_date'])->format('M d, Y') }} - 
                                {{ \Carbon\Carbon::parse($budgetProgress['end_date'])->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget Details Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Details</h3>
                        
                        <div class="mb-3">
                            <div class="text-sm text-gray-600">Name</div>
                            <div class="font-medium text-gray-900">{{ $budget->name }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="text-sm text-gray-600">Category</div>
                            <div class="font-medium text-gray-900">
                                @if($budget->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                        style="background-color: {{ $budget->category->color }}20; color: {{ $budget->category->color }};">
                                        <i class="fas fa-{{ $budget->category->icon }} mr-1"></i>
                                        {{ $budget->category->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">No Category</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($budget->notes)
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Notes</div>
                                <div class="text-gray-900">{{ $budget->notes }}</div>
                            </div>
                        @endif
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-600 mb-1">Created</div>
                            <div class="text-gray-900">{{ $budget->created_at->format('M d, Y H:i') }}</div>
                            
                            <div class="text-sm text-gray-600 mt-2 mb-1">Last Updated</div>
                            <div class="text-gray-900">{{ $budget->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Budget Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                    
                    <form action="{{ route('budgets.destroy', $budget) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this budget? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 mr-4">Delete this budget and all of its data</span>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Delete Budget
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
