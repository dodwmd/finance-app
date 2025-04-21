<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" name="description" id="description" value="{{ old('description', $transaction->description) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('description')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="amount" id="amount" value="{{ old('amount', $transaction->amount) }}" step="0.01" min="0.01" required
                                    class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            @error('amount')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" id="type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="income" {{ (old('type', $transaction->type) === 'income') ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ (old('type', $transaction->type) === 'expense') ? 'selected' : '' }}>Expense</option>
                                <option value="transfer" {{ (old('type', $transaction->type) === 'transfer') ? 'selected' : '' }}>Transfer</option>
                            </select>
                            @error('type')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category_id" id="category_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <optgroup label="Income" data-type="income">
                                    @foreach($incomeCategories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id', $transaction->category_id) == $category->id) ? 'selected' : '' }} data-icon="{{ $category->icon }}" data-color="{{ $category->color }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Expense" data-type="expense">
                                    @foreach($expenseCategories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id', $transaction->category_id) == $category->id) ? 'selected' : '' }} data-icon="{{ $category->icon }}" data-color="{{ $category->color }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Transfer" data-type="transfer">
                                    @foreach($transferCategories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id', $transaction->category_id) == $category->id) ? 'selected' : '' }} data-icon="{{ $category->icon }}" data-color="{{ $category->color }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('category_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Transaction Date -->
                        <div class="mb-6">
                            <label for="transaction_date" class="block text-sm font-medium text-gray-700">Transaction Date</label>
                            <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('transaction_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('transactions.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Update Transaction') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const categorySelect = document.getElementById('category_id');
            const optgroups = categorySelect.querySelectorAll('optgroup');
            
            function filterCategories() {
                const selectedType = typeSelect.value;
                
                // Hide all option groups first
                optgroups.forEach(group => {
                    const groupType = group.getAttribute('data-type');
                    if (groupType === selectedType) {
                        group.style.display = '';
                        
                        // If no option is selected in the visible group, select the first one
                        let hasSelectedOption = false;
                        Array.from(group.querySelectorAll('option')).forEach(option => {
                            if (option.selected) {
                                hasSelectedOption = true;
                            }
                        });
                        
                        if (!hasSelectedOption && group.querySelector('option')) {
                            group.querySelector('option').selected = true;
                        }
                    } else {
                        group.style.display = 'none';
                        // Deselect options in hidden groups
                        Array.from(group.querySelectorAll('option')).forEach(option => {
                            option.selected = false;
                        });
                    }
                });
            }
            
            // Initial filter
            filterCategories();
            
            // Filter on type change
            typeSelect.addEventListener('change', filterCategories);
        });
    </script>
</x-app-layout>
