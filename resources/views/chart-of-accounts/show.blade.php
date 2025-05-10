<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('View Chart of Account') }}: {{ $chartOfAccount->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Account Code') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->account_code }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->name }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Type') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->type }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Description') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->description ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Parent Account') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->parent ? $chartOfAccount->parent->name . ' (' . $chartOfAccount->parent->account_code . ')' : '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Is Active') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->is_active ? __('Yes') : __('No') }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Allow Direct Posting') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->allow_direct_posting ? __('Yes') : __('No') }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('System Account Tag') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->system_account_tag ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created At') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->created_at->format(config('app.datetime_format', 'Y-m-d H:i:s')) }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Updated At') }}</p>
                            <p class="text-lg">{{ $chartOfAccount->updated_at->format(config('app.datetime_format', 'Y-m-d H:i:s')) }}</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-start space-x-3">
                        <a href="{{ route('chart-of-accounts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:border-gray-500 dark:focus:border-gray-500 focus:ring focus:ring-gray-200 dark:focus:ring-gray-600 active:bg-gray-500 dark:active:bg-gray-500 disabled:opacity-25 transition">
                            {{ __('Back to List') }}
                        </a>
                        <a href="{{ route('chart-of-accounts.edit', $chartOfAccount) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-300 active:bg-blue-800 disabled:opacity-25 transition">
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
