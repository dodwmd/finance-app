<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bank Account Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('bank-accounts.index') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                            &larr; Back to All Accounts
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Account Name</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $bankAccount->account_name }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Account Type</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $bankAccount->type)) }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Account Number</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $bankAccount->account_number ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">BSB</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $bankAccount->formatted_bsb ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Opening Balance</h3>
                            <p class="mt-1 text-sm text-gray-600">${{ number_format($bankAccount->opening_balance, 2) }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Current Balance</h3>
                            <p class="mt-1 text-sm text-gray-600">${{ number_format($bankAccount->current_balance, 2) }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Created At</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $bankAccount->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Last Updated</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $bankAccount->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 space-x-3">
                        <a href="{{ route('bank-accounts.deposits.create', $bankAccount) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Record Deposit
                        </a>
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-50 transition ease-in-out duration-150" title="Record Withdrawal - Coming Soon" disabled>
                            Record Withdrawal
                        </button>
                        <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Edit Account
                        </a>
                    </div>

                    {{-- Placeholder for related transactions list --}}
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900">Related Transactions</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            (Transaction history will be displayed here in a future update.)
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
