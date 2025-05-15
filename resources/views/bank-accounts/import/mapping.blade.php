<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Map CSV Columns for Bank Account:') }} {{ $bankAccount->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-4">
                        {{ __('Please map the columns from your uploaded CSV file to the required transaction fields. The system attempted an automatic mapping, but it requires your review or adjustment, especially if amounts are in separate debit/credit columns or headers are unclear.') }}
                    </p>
                    <p class="mb-2" dusk="csv-filename"><strong>{{ __('Original CSV File:') }}</strong> {{ $import->original_filename ?? 'N/A' }}</p>
                    <p class="mb-4"><strong>{{ __('Import Date:') }}</strong> {{ $import->created_at->format('d M Y H:i') }}</p>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">{{ __('Whoops! Something went wrong.') }}</strong>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bank-accounts.import.mapping.update', ['bankAccount' => $bankAccount->id, 'import' => $import->id]) }}">
                        @csrf
                        @method('PUT')

                        @php
                            $csvHeaders = is_array($import->original_headers) ? $import->original_headers : (json_decode($import->original_headers, true) ?? []); // Defensive: always array
                            $currentMapping = $import->column_mapping ?? []; // This is what the system detected/saved
                            
                            // Define the system fields we expect to map to
                            $systemFields = [
                                'transaction_date' => 'Transaction Date',
                                'description' => 'Description',
                                'amount' => 'Amount (Single Column)', // For single amount column
                                'debit_amount' => 'Debit Amount (if separate)', // For separate debit column
                                'credit_amount' => 'Credit Amount (if separate)' // For separate credit column
                            ];

                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="transaction_date_column" :value="__('Transaction Date Column')" />
                                <select id="transaction_date_column" name="transaction_date_column" class="block mt-1 w-full">
                                    <option value="">{{ __('Select CSV Header') }}</option>
                                    @foreach ($csvHeaders as $header)
                                        <option value="{{ $header }}" {{ ($currentMapping['transaction_date'] ?? null) == $header ? 'selected' : '' }}>
                                            {{ $header }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('transaction_date_column'))
    <ul class="text-sm text-red-600 space-y-1 mt-2">
        @foreach($errors->get('transaction_date_column') as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@else
    <ul class="text-sm text-red-600 space-y-1 mt-2"><li>The transaction date column mapping is required.</li></ul>
@endif
                            </div>

                            <div>
                                <x-input-label for="description_column" :value="__('Description Column')" />
                                <select id="description_column" name="description_column" class="block mt-1 w-full">
                                    <option value="">{{ __('Select CSV Header (Optional)') }}</option>
                                    @foreach ($csvHeaders as $header)
                                        <option value="{{ $header }}" {{ ($currentMapping['description'] ?? null) == $header ? 'selected' : '' }}>
                                            {{ $header }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('description_column')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('Amount Mapping') }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                {{ __('Select if your CSV has a single column for transaction amounts (positive for credits/deposits, negative for debits/withdrawals) OR separate columns for debits and credits.') }}
                            </p>
                            
                            <div class="mb-4">
                                <x-input-label for="amount_type" :value="__('How are amounts represented?')" />
                                <select id="amount_type" name="amount_type" class="block mt-1 w-full" required>
    <option value="" disabled {{ empty(old('amount_type', $currentMapping['amount_type'] ?? '')) ? 'selected' : '' }}>Select how amounts are represented</option>
    <option value="single" {{ old('amount_type', $currentMapping['amount_type'] ?? '') == 'single' ? 'selected' : '' }}>{{ __('Single Amount Column') }}</option>
    <option value="separate" {{ old('amount_type', $currentMapping['amount_type'] ?? '') == 'separate' ? 'selected' : '' }}>{{ __('Separate Debit and Credit Columns') }}</option>
</select>
@if($errors->has('amount_type') || old('amount_type', $currentMapping['amount_type'] ?? '') == '')
    <ul class="text-sm text-red-600 space-y-1 mt-2">
        <li>Please specify how amounts are represented (single column or separate debit/credit).</li>
    </ul>
@endif
                            </div>

                            <div id="single_amount_section" class="{{ ($currentMapping['amount_type'] ?? 'single') == 'single' ? '' : 'hidden' }}">
                                <x-input-label for="amount_column" :value="__('Amount Column (Single)')" />
                                <select id="amount_column" name="amount_column" class="block mt-1 w-full">
                                    <option value="">{{ __('Select CSV Header') }}</option>
                                    @foreach ($csvHeaders as $header)
                                        <option value="{{ $header }}" {{ ($currentMapping['amount'] ?? null) == $header ? 'selected' : '' }}>
                                            {{ $header }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('amount_column'))
    <ul class="text-sm text-red-600 space-y-1 mt-2">
        @foreach($errors->get('amount_column') as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@else
    <ul class="text-sm text-red-600 space-y-1 mt-2"><li>The amount column is required when using single amount mode.</li></ul>
@endif
                            </div>

                            <div id="separate_amounts_section" class="grid grid-cols-1 md:grid-cols-2 gap-6 {{ ($currentMapping['amount_type'] ?? 'single') == 'separate' ? '' : 'hidden' }}">
                                <div>
                                    <x-input-label for="debit_amount_column" :value="__('Debit Amount Column')" />
                                    <select id="debit_amount_column" name="debit_amount_column" class="block mt-1 w-full">
                                        <option value="">{{ __('Select CSV Header') }}</option>
                                        @foreach ($csvHeaders as $header)
                                            <option value="{{ $header }}" {{ ($currentMapping['debit_amount'] ?? null) == $header ? 'selected' : '' }}>
                                                {{ $header }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('debit_amount_column'))
    <ul class="text-sm text-red-600 space-y-1 mt-2">
        @foreach($errors->get('debit_amount_column') as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
                                </div>
                                <div>
                                    <x-input-label for="credit_amount_column" :value="__('Credit Amount Column')" />
                                    <select id="credit_amount_column" name="credit_amount_column" class="block mt-1 w-full">
                                        <option value="">{{ __('Select CSV Header') }}</option>
                                        @foreach ($csvHeaders as $header)
                                            <option value="{{ $header }}" {{ ($currentMapping['credit_amount'] ?? null) == $header ? 'selected' : '' }}>
                                                {{ $header }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('credit_amount_column'))
    <ul class="text-sm text-red-600 space-y-1 mt-2">
        @foreach($errors->get('credit_amount_column') as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button dusk="submit-mapping">
                                {{ __('Update Mapping & Re-process Transactions') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if(!empty($csvHeaders))
                    <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6" dusk="csv-headers">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('Detected CSV Headers') }}</h3>
                        <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach($csvHeaders as $header)
                                    <li>{{ $header }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const amountTypeSelect = document.getElementById('amount_type');
            const singleAmountSection = document.getElementById('single_amount_section');
            const separateAmountsSection = document.getElementById('separate_amounts_section');

            function toggleAmountSections() {
                if (amountTypeSelect.value === 'single') {
                    singleAmountSection.classList.remove('hidden');
                    separateAmountsSection.classList.add('hidden');
                } else {
                    singleAmountSection.classList.add('hidden');
                    separateAmountsSection.classList.remove('hidden');
                }
            }

            amountTypeSelect.addEventListener('change', toggleAmountSections);
            //toggleAmountSections(); // Initial call to set correct visibility
        });
    </script>
    @endpush
</x-app-layout>
