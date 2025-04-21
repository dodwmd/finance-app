<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Budget') }}
            </h2>
            <a href="{{ route('budgets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Back to Budgets') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('budgets.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Budget Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center">
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                                <button type="button" id="newCategoryBtn" class="text-xs text-indigo-600 hover:text-indigo-500">
                                    + Create New Category
                                </button>
                            </div>
                            <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required style="pointer-events: auto; opacity: 1;">
                                <option value="">Select a category</option>
                                <optgroup label="Income Categories">
                                    @foreach($categories->where('type', 'income') as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Expense Categories">
                                    @foreach($categories->where('type', 'expense') as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Transfer Categories">
                                    @foreach($categories->where('type', 'transfer') as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- Debug information -->
                            <div class="mt-2 p-2 bg-gray-100 rounded text-xs">
                                <p>Available categories: {{ $categories->count() }}</p>
                                <p>Income: {{ $categories->where('type', 'income')->count() }}</p>
                                <p>Expense: {{ $categories->where('type', 'expense')->count() }}</p>
                                <p>Transfer: {{ $categories->where('type', 'transfer')->count() }}</p>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium text-gray-700">Budget Amount</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount') }}" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Period -->
                        <div class="mb-4">
                            <label for="period" class="block text-sm font-medium text-gray-700">Budget Period</label>
                            <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                @foreach($periodOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('period') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('period')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="mb-4">
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date (Optional) -->
                        <div class="mb-4">
                            <label for="end_date" class="block text-sm font-medium text-gray-700">
                                End Date (Optional - will be calculated based on period if not provided)
                            </label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                    Active Budget
                                </label>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" dusk="submit-create-budget" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create Budget
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div id="categoryModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Create New Category
                            </h3>
                            <div class="mt-4 w-full">
                                <form id="categoryForm" method="POST" action="{{ route('categories.store.api') }}">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                                        <input type="text" name="name" id="category_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                        <p class="mt-1 text-sm text-red-600 hidden" id="name-error"></p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="type" class="block text-sm font-medium text-gray-700">Category Type</label>
                                        <select name="type" id="category_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                            <option value="">Select Type</option>
                                            <option value="income">Income</option>
                                            <option value="expense">Expense</option>
                                            <option value="transfer">Transfer</option>
                                        </select>
                                        <p class="mt-1 text-sm text-red-600 hidden" id="type-error"></p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
                                        <input type="color" name="color" id="category_color" value="#4CAF50" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-10 w-full">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="icon" class="block text-sm font-medium text-gray-700">Icon (Optional)</label>
                                        <select name="icon" id="category_icon" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="money-bill">Money Bill</option>
                                            <option value="home">Home</option>
                                            <option value="car">Car</option>
                                            <option value="utensils">Utensils</option>
                                            <option value="shopping-cart">Shopping</option>
                                            <option value="heartbeat">Health</option>
                                            <option value="graduation-cap">Education</option>
                                            <option value="bolt">Utilities</option>
                                            <option value="film">Entertainment</option>
                                            <option value="gift">Gift</option>
                                            <option value="exchange-alt">Transfer</option>
                                            <option value="laptop">Laptop</option>
                                            <option value="chart-line">Investment</option>
                                            <option value="plus-circle">Other</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="saveCategory" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Category
                    </button>
                    <button type="button" id="cancelCategory" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periodSelect = document.getElementById('period');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            // Calculate end date when period or start date changes
            function updateEndDate() {
                const period = periodSelect.value;
                const startDate = new Date(startDateInput.value);
                
                if (!startDate || isNaN(startDate.getTime())) {
                    return;
                }
                
                let endDate = new Date(startDate);
                
                if (period === 'monthly') {
                    endDate.setMonth(endDate.getMonth() + 1);
                    endDate.setDate(endDate.getDate() - 1);
                } else if (period === 'quarterly') {
                    endDate.setMonth(endDate.getMonth() + 3);
                    endDate.setDate(endDate.getDate() - 1);
                } else if (period === 'yearly') {
                    endDate.setFullYear(endDate.getFullYear() + 1);
                    endDate.setDate(endDate.getDate() - 1);
                }
                
                // Format the date as YYYY-MM-DD
                const formattedEndDate = endDate.toISOString().split('T')[0];
                endDateInput.value = formattedEndDate;
            }
            
            // Set initial end date
            updateEndDate();
            
            // Update end date when period or start date changes
            periodSelect.addEventListener('change', updateEndDate);
            startDateInput.addEventListener('change', updateEndDate);
            
            // Category Modal Handling
            const newCategoryBtn = document.getElementById('newCategoryBtn');
            const categoryModal = document.getElementById('categoryModal');
            const cancelCategory = document.getElementById('cancelCategory');
            const saveCategory = document.getElementById('saveCategory');
            const categoryForm = document.getElementById('categoryForm');
            const categorySelect = document.getElementById('category_id');
            const categoryTypeSelect = document.getElementById('category_type');
            
            // Open modal
            newCategoryBtn.addEventListener('click', function() {
                categoryModal.classList.remove('hidden');
            });
            
            // Close modal
            cancelCategory.addEventListener('click', function() {
                categoryModal.classList.add('hidden');
                resetCategoryForm();
            });
            
            // Pre-select category type based on current tab
            categoryTypeSelect.addEventListener('change', function() {
                const iconSelect = document.getElementById('category_icon');
                const colorInput = document.getElementById('category_color');
                
                // Set default icon based on type
                switch (categoryTypeSelect.value) {
                    case 'income':
                        iconSelect.value = 'money-bill';
                        colorInput.value = '#4CAF50';
                        break;
                    case 'expense':
                        iconSelect.value = 'shopping-cart';
                        colorInput.value = '#E91E63';
                        break;
                    case 'transfer':
                        iconSelect.value = 'exchange-alt';
                        colorInput.value = '#2196F3';
                        break;
                }
            });
            
            // Save category with AJAX
            saveCategory.addEventListener('click', function() {
                const formData = new FormData(categoryForm);
                const nameError = document.getElementById('name-error');
                const typeError = document.getElementById('type-error');
                
                // Reset errors
                nameError.classList.add('hidden');
                typeError.classList.add('hidden');
                
                fetch(categoryForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add new category to select dropdown
                        const newCategory = data.category;
                        let optgroup;
                        
                        // Find the right optgroup based on category type
                        switch (newCategory.type) {
                            case 'income':
                                optgroup = categorySelect.querySelector('optgroup[label="Income Categories"]');
                                break;
                            case 'expense':
                                optgroup = categorySelect.querySelector('optgroup[label="Expense Categories"]');
                                break;
                            case 'transfer':
                                optgroup = categorySelect.querySelector('optgroup[label="Transfer Categories"]');
                                break;
                        }
                        
                        if (optgroup) {
                            const option = document.createElement('option');
                            option.value = newCategory.id;
                            option.textContent = newCategory.name;
                            option.selected = true;
                            optgroup.appendChild(option);
                        }
                        
                        // Close modal and reset form
                        categoryModal.classList.add('hidden');
                        resetCategoryForm();
                    } else {
                        // Display validation errors
                        if (data.errors) {
                            if (data.errors.name) {
                                nameError.textContent = data.errors.name[0];
                                nameError.classList.remove('hidden');
                            }
                            if (data.errors.type) {
                                typeError.textContent = data.errors.type[0];
                                typeError.classList.remove('hidden');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error creating category:', error);
                });
            });
            
            function resetCategoryForm() {
                categoryForm.reset();
                document.getElementById('name-error').classList.add('hidden');
                document.getElementById('type-error').classList.add('hidden');
            }
        });
    </script>
</x-app-layout>
