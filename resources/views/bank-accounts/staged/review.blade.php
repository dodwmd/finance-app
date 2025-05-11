<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Staged Transactions for ') }} {{ $bankAccount->account_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($stagedTransactions->isEmpty())
                        <p>There are no transactions currently pending review for this account.</p>
                        <div class="mt-4">
                            <a href="{{ route('bank-accounts.import.form', $bankAccount) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Import New Statement
                            </a>
                             <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="ml-4 underline text-sm text-gray-600 hover:text-gray-900">
                                Back to Account Details
                            </a>
                        </div>
                    @else
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">Review the transactions below. You can categorize, match, or approve them for import.</p>
                             <a href="{{ route('bank-accounts.show', $bankAccount) }}" class="ml-4 underline text-sm text-gray-600 hover:text-gray-900">
                                Back to Account Details
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($stagedTransactions as $st)
                                        <tr class="{{ $st->status === 'potential_duplicate' ? 'bg-orange-50 hover:bg-orange-100' : 'hover:bg-gray-50' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $st->transaction_date->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ Str::limit($st->description, 40) }}
                                                @if($st->status === 'potential_duplicate')
                                                    <span class="ml-1 text-xs text-orange-600">(Dup?)</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-mono @if($st->amount < 0) text-red-600 @else text-green-600 @endif">{{ number_format($st->amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $st->type }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($st->status === 'pending_review' || $st->status === 'potential_duplicate')
                                                    <form method="POST" action="{{ route('staged-transactions.update-category', ['stagedTransaction' => $st, 'page' => $stagedTransactions->currentPage()]) }}" class="inline-flex items-center">
                                                        @csrf
                                                        <select name="category_id" class="text-xs border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mr-2" onchange="this.form.submit()">
                                                            <option value="">-- Select Category --</option>
                                                            @foreach($categories->groupBy('type') as $type => $cats)
                                                                <optgroup label="{{ Str::title($type) }}">
                                                                    @foreach($cats as $category)
                                                                        <option value="{{ $category->id }}" {{ $st->suggested_category_id == $category->id ? 'selected' : '' }}>
                                                                            {{ $category->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endforeach
                                                        </select>
                                                        {{-- Minimalist update button if auto-submit on change is not preferred --}}
                                                        {{-- <button type="submit" class="text-xs px-1 py-0.5 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Set</button> --}}
                                                    </form>
                                                @elseif($st->suggestedCategory)
                                                    {{ $st->suggestedCategory->name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($st->status === 'pending_review') bg-yellow-100 text-yellow-800 
                                                    @elseif($st->status === 'imported') bg-green-100 text-green-800 
                                                    @elseif($st->status === 'ignored') bg-gray-100 text-gray-800 
                                                    @elseif($st->status === 'potential_duplicate') bg-orange-100 text-orange-800 
                                                    @else bg-gray-100 text-gray-800 
                                                    @endif">
                                                    {{ Str::title(str_replace('_', ' ', $st->status)) }}
                                                </span>
                                                @if($st->status === 'potential_duplicate' && $st->matchedTransaction)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Matches Txn: <a href="{{ route('transactions.show', $st->matchedTransaction->id) }}" target="_blank" class="text-blue-600 hover:underline">#{{ $st->matchedTransaction->id }}</a><br>
                                                        ({{ $st->matchedTransaction->transaction_date->format('Y-m-d') }}, {{ Str::limit($st->matchedTransaction->description, 20) }}, {{ number_format($st->matchedTransaction->amount, 2) }})
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($st->status === 'pending_review' || $st->status === 'potential_duplicate')
                                                    <form method="POST" action="{{ route('staged-transactions.approve', ['stagedTransaction' => $st, 'page' => $stagedTransactions->currentPage()]) }}" class="inline-block mr-2">
                                                        @csrf
                                                        <button type="submit" class="text-xs px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('staged-transactions.ignore', ['stagedTransaction' => $st, 'page' => $stagedTransactions->currentPage()]) }}" class="inline-block ml-1">
                                                        @csrf
                                                        <button type="submit" class="text-xs px-2 py-1 {{ $st->status === 'potential_duplicate' ? 'bg-orange-500 hover:bg-orange-600 focus:ring-orange-500' : 'bg-red-500 hover:bg-red-600 focus:ring-red-500' }} text-white rounded focus:outline-none focus:ring-2 focus:ring-opacity-50" onclick="return confirm('Are you sure you want to ignore this transaction?');">
                                                            Ignore
                                                        </button>
                                                    </form>
                                                @elseif($st->status === 'imported' && $st->matchedTransaction)
                                                    <a href="{{ route('transactions.show', $st->matchedTransaction->id) }}" class="text-xs text-blue-600 hover:underline">View Txn</a>
                                                @else
                                                    <span class="text-xs text-gray-500">Processed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $stagedTransactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
