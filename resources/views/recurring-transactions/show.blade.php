<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Recurring Transaction Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('recurring-transactions.edit', $recurringTransaction->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <form action="{{ route('recurring-transactions.toggle-status', $recurringTransaction->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-4 py-2 {{ $recurringTransaction->status === 'active' ? 'bg-yellow-600' : 'bg-green-600' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:{{ $recurringTransaction->status === 'active' ? 'bg-yellow-700' : 'bg-green-700' }} focus:bg-{{ $recurringTransaction->status === 'active' ? 'yellow' : 'green' }}-700 active:bg-{{ $recurringTransaction->status === 'active' ? 'yellow' : 'green' }}-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ $recurringTransaction->status === 'active' ? __('Pause') : __('Activate') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">General Information</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h4>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $recurringTransaction->description }}</p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</h4>
                                    <p class="mt-1 {{ $recurringTransaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        ${{ number_format($recurringTransaction->amount, 2) }}
                                    </p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</h4>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $recurringTransaction->type === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 
                                               ($recurringTransaction->type === 'expense' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 
                                               'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300') }}">
                                            {{ ucfirst($recurringTransaction->type) }}
                                        </span>
                                    </p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</h4>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $recurringTransaction->category->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Schedule Information</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Frequency</h4>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ ucfirst($recurringTransaction->frequency) }}</p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h4>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $recurringTransaction->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ ucfirst($recurringTransaction->status) }}
                                        </span>
                                    </p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</h4>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $recurringTransaction->start_date->format('F j, Y') }}</p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</h4>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">
                                        {{ $recurringTransaction->end_date ? $recurringTransaction->end_date->format('F j, Y') : 'No end date (ongoing)' }}
                                    </p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Due Date</h4>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $recurringTransaction->next_due_date->format('F j, Y') }}</p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Processed Date</h4>
                                    <p class="mt-1 text-gray-900 dark:text-gray-100">
                                        {{ $recurringTransaction->last_processed_date ? $recurringTransaction->last_processed_date->format('F j, Y') : 'Not processed yet' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('recurring-transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Back to List') }}
                        </a>
                        
                        <form action="{{ route('recurring-transactions.destroy', $recurringTransaction->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this recurring transaction? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
