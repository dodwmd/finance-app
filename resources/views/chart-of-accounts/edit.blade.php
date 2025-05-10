<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Account') }}: {{ $chartOfAccount->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Update Account Details</h3>

                    @if ($errors->any())
                        <div class="mb-4">
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('chart-of-accounts.update', $chartOfAccount) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Account Code -->
                        <div>
                            <x-input-label for="account_code" :value="__('Account Code')" />
                            <x-text-input id="account_code" class="block mt-1 w-full" type="text" name="account_code" :value="old('account_code', $chartOfAccount->account_code)" required autofocus autocomplete="off" dusk="account_code" />
                            <x-input-error :messages="$errors->get('account_code')" class="mt-2" />
                        </div>

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Account Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $chartOfAccount->name)" required autocomplete="off" dusk="account_name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Type -->
                        <div>
                            <x-input-label for="type" :value="__('Account Type')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" dusk="account_type" required>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type }}" {{ old('type', $chartOfAccount->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" dusk="description">{{ old('description', $chartOfAccount->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Parent Account -->
                        <div>
                            <x-input-label for="parent_account_id" :value="__('Parent Account (Optional)')" />
                            <select id="parent_account_id" name="parent_account_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" dusk="parent_account_id">
                                <option value="">{{ __('None') }}</option>
                                @foreach($parentAccounts as $parentAccount)
                                    <option value="{{ $parentAccount->id }}" {{ old('parent_account_id', $chartOfAccount->parent_account_id) == $parentAccount->id ? 'selected' : '' }}>
                                        {{ $parentAccount->account_code }} - {{ $parentAccount->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('parent_account_id')" class="mt-2" />
                        </div>

                        <!-- System Account Tag -->
                        <div>
                            <x-input-label for="system_account_tag" :value="__('System Account Tag (Optional)')" />
                            <x-text-input id="system_account_tag" class="block mt-1 w-full" type="text" name="system_account_tag" :value="old('system_account_tag', $chartOfAccount->system_account_tag)" autocomplete="off" dusk="system_account_tag" />
                            <x-input-error :messages="$errors->get('system_account_tag')" class="mt-2" />
                        </div>

                        <!-- Is Active -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', $chartOfAccount->is_active) ? 'checked' : '' }} dusk="is_active">
                                <span class="ml-2 text-sm text-gray-600">{{ __('Is Active') }}</span>
                            </label>
                        </div>

                        <!-- Allow Direct Posting -->
                        <div class="block mt-4">
                            <label for="allow_direct_posting" class="inline-flex items-center">
                                <input type="hidden" name="allow_direct_posting" value="0">
                                <input id="allow_direct_posting" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="allow_direct_posting" value="1" {{ old('allow_direct_posting', $chartOfAccount->allow_direct_posting) ? 'checked' : '' }} dusk="allow_direct_posting">
                                <span class="ml-2 text-sm text-gray-600">{{ __('Allow Direct Posting') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('chart-of-accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ml-3" dusk="submit-button">
                                {{ __('Update Account') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
