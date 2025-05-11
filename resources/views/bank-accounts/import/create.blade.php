<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Statement for ') }} {{ $bankAccount->account_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- The route for handling the upload will be 'bank-accounts.import.store' --}}
                    <form method="POST" action="{{ route('bank-accounts.import.store', $bankAccount) }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Statement File -->
                        <div class="mt-4">
                            <x-input-label for="statement_file" :value="__('Statement File (CSV only for now)')" />
                            <input id="statement_file" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" type="file" name="statement_file" required accept=".csv" />
                            <x-input-error :messages="$errors->get('statement_file')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-3">
                                {{ __('Import Statement') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
