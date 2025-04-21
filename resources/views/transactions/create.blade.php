<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" name="description" id="description" value="{{ old('description') }}" required
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
                                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
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
                                <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                                <option value="transfer" {{ old('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                            </select>
                            @error('type')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category" id="category" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <optgroup label="Income">
                                    <option value="Salary" {{ old('category') === 'Salary' ? 'selected' : '' }}>Salary</option>
                                    <option value="Investment" {{ old('category') === 'Investment' ? 'selected' : '' }}>Investment</option>
                                    <option value="Gift" {{ old('category') === 'Gift' ? 'selected' : '' }}>Gift</option>
                                    <option value="Other Income" {{ old('category') === 'Other Income' ? 'selected' : '' }}>Other Income</option>
                                </optgroup>
                                <optgroup label="Expense">
                                    <option value="Housing" {{ old('category') === 'Housing' ? 'selected' : '' }}>Housing</option>
                                    <option value="Transportation" {{ old('category') === 'Transportation' ? 'selected' : '' }}>Transportation</option>
                                    <option value="Food" {{ old('category') === 'Food' ? 'selected' : '' }}>Food</option>
                                    <option value="Utilities" {{ old('category') === 'Utilities' ? 'selected' : '' }}>Utilities</option>
                                    <option value="Entertainment" {{ old('category') === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                                    <option value="Shopping" {{ old('category') === 'Shopping' ? 'selected' : '' }}>Shopping</option>
                                    <option value="Health" {{ old('category') === 'Health' ? 'selected' : '' }}>Health</option>
                                    <option value="Education" {{ old('category') === 'Education' ? 'selected' : '' }}>Education</option>
                                    <option value="Other Expense" {{ old('category') === 'Other Expense' ? 'selected' : '' }}>Other Expense</option>
                                </optgroup>
                                <optgroup label="Transfer">
                                    <option value="Account Transfer" {{ old('category') === 'Account Transfer' ? 'selected' : '' }}>Account Transfer</option>
                                </optgroup>
                            </select>
                            @error('category')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Transaction Date -->
                        <div class="mb-6">
                            <label for="transaction_date" class="block text-sm font-medium text-gray-700">Transaction Date</label>
                            <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required
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
                                {{ __('Create Transaction') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
