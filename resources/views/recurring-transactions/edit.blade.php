<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Recurring Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('recurring-transactions.update', $recurringTransaction->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" :value="old('description', $recurringTransaction->description)" required autofocus />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Amount -->
                        <div>
                            <x-input-label for="amount" :value="__('Amount')" />
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <x-text-input id="amount" class="block mt-1 w-full pl-7" type="number" name="amount" :value="old('amount', $recurringTransaction->amount)" step="0.01" min="0.01" required />
                            </div>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Transaction Type -->
                        <div>
                            <x-input-label for="type" :value="__('Transaction Type')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="income" {{ (old('type', $recurringTransaction->type) === 'income') ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ (old('type', $recurringTransaction->type) === 'expense') ? 'selected' : '' }}>Expense</option>
                                <option value="transfer" {{ (old('type', $recurringTransaction->type) === 'transfer') ? 'selected' : '' }}>Transfer</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div id="category-container">
                            <x-input-label for="category_id" :value="__('Category')" />
                            
                            <!-- Income Categories -->
                            <div id="income-categories" class="mt-1">
                                <select id="income_category_id" name="category_id" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="" disabled>Select Income Category</option>
                                    @foreach ($incomeCategories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id', $recurringTransaction->category_id) == $category->id) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Expense Categories -->
                            <div id="expense-categories" class="mt-1 hidden">
                                <select id="expense_category_id" name="category_id" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="" disabled>Select Expense Category</option>
                                    @foreach ($expenseCategories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id', $recurringTransaction->category_id) == $category->id) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Transfer Categories -->
                            <div id="transfer-categories" class="mt-1 hidden">
                                <select id="transfer_category_id" name="category_id" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="" disabled>Select Transfer Category</option>
                                    @foreach ($transferCategories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id', $recurringTransaction->category_id) == $category->id) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <!-- Frequency -->
                        <div>
                            <x-input-label for="frequency" :value="__('Frequency')" />
                            <select id="frequency" name="frequency" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="daily" {{ (old('frequency', $recurringTransaction->frequency) === 'daily') ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ (old('frequency', $recurringTransaction->frequency) === 'weekly') ? 'selected' : '' }}>Weekly</option>
                                <option value="biweekly" {{ (old('frequency', $recurringTransaction->frequency) === 'biweekly') ? 'selected' : '' }}>Bi-weekly</option>
                                <option value="monthly" {{ (old('frequency', $recurringTransaction->frequency) === 'monthly') ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ (old('frequency', $recurringTransaction->frequency) === 'quarterly') ? 'selected' : '' }}>Quarterly</option>
                                <option value="annually" {{ (old('frequency', $recurringTransaction->frequency) === 'annually') ? 'selected' : '' }}>Annually</option>
                            </select>
                            <x-input-error :messages="$errors->get('frequency')" class="mt-2" />
                        </div>

                        <!-- Start Date -->
                        <div>
                            <x-input-label for="start_date" :value="__('Start Date')" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $recurringTransaction->start_date->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <!-- End Date (Optional) -->
                        <div>
                            <x-input-label for="end_date" :value="__('End Date (Optional)')" />
                            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $recurringTransaction->end_date ? $recurringTransaction->end_date->format('Y-m-d') : '')" />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave empty for an indefinite recurring transaction.</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="active" {{ (old('status', $recurringTransaction->status) === 'active') ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ (old('status', $recurringTransaction->status) === 'paused') ? 'selected' : '' }}>Paused</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('recurring-transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Recurring Transaction') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Category selection based on transaction type
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const incomeCategories = document.getElementById('income-categories');
            const expenseCategories = document.getElementById('expense-categories');
            const transferCategories = document.getElementById('transfer-categories');
            const incomeCategorySelect = document.getElementById('income_category_id');
            const expenseCategorySelect = document.getElementById('expense_category_id');
            const transferCategorySelect = document.getElementById('transfer_category_id');

            // Function to show the appropriate category dropdown
            function updateCategoryDropdown() {
                // First, disable all category selects
                incomeCategorySelect.disabled = true;
                expenseCategorySelect.disabled = true;
                transferCategorySelect.disabled = true;
                
                // Hide all category containers
                incomeCategories.classList.add('hidden');
                expenseCategories.classList.add('hidden');
                transferCategories.classList.add('hidden');
                
                // Show and enable the appropriate one based on selected type
                if (typeSelect.value === 'income') {
                    incomeCategories.classList.remove('hidden');
                    incomeCategorySelect.disabled = false;
                } else if (typeSelect.value === 'expense') {
                    expenseCategories.classList.remove('hidden');
                    expenseCategorySelect.disabled = false;
                } else if (typeSelect.value === 'transfer') {
                    transferCategories.classList.remove('hidden');
                    transferCategorySelect.disabled = false;
                }
            }

            // Initial setup
            updateCategoryDropdown();
            
            // Update on change
            typeSelect.addEventListener('change', updateCategoryDropdown);
        });
    </script>
</x-app-layout>
