<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Bank Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('bank-accounts.store') }}">
                        @csrf

                        <!-- Account Name (User Facing) -->
                        <div class="mb-4">
                            <x-input-label for="account_name" :value="__('Account Name')" />
                            <x-text-input id="account_name" class="block mt-1 w-full" type="text" name="account_name" :value="old('account_name')" required autofocus dusk="account-name-input" />
                            <x-input-error :messages="$errors->get('account_name')" class="mt-2" />
                        </div>

                        <!-- Account Type -->
                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Account Type')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" dusk="account-type-select">
                                <option value="bank" {{ old('type') == 'bank' ? 'selected' : '' }}>Bank Account</option>
                                <option value="credit_card" {{ old('type') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="cash" {{ old('type') == 'cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Account Number -->
                        <div class="mb-4">
                            <x-input-label for="account_number" :value="__('Account Number (Optional)')" />
                            <x-text-input id="account_number" class="block mt-1 w-full" type="text" name="account_number" :value="old('account_number')" dusk="account-number-input" />
                            <x-input-error :messages="$errors->get('account_number')" class="mt-2" />
                        </div>

                        <!-- BSB -->
                        <div class="mb-4">
                            <x-input-label for="bsb" :value="__('BSB (Optional)')" />
                            <x-text-input id="bsb" class="block mt-1 w-full" type="text" name="bsb" :value="old('bsb')" dusk="bsb-input" />
                            <x-input-error :messages="$errors->get('bsb')" class="mt-2" />
                        </div>

                        <!-- Opening Balance -->
                        <div class="mb-4">
                            <x-input-label for="opening_balance" :value="__('Opening Balance')" />
                            <x-text-input id="opening_balance" class="block mt-1 w-full" type="number" name="opening_balance" :value="old('opening_balance', 0.00)" step="0.01" required dusk="opening-balance-input" />
                            <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('bank-accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button dusk="create-account-button">
                                {{ __('Create Account') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
