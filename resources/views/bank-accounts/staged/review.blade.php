<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Staged Transactions for ') }} {{ $bankAccount->account_name }}
        </h2>
    </x-slot>
    
    <script>
        document.addEventListener('alpine:init', function() {
            Alpine.data('manualMatchComponent', function() {
                return {
                    showModal: false,
                    stagedTransactionId: null,
                    stagedDate: '',
                    stagedDescription: '',
                    stagedAmount: 0.00,
                    searchTerm: '',
                    searchDateFrom: '',
                    searchDateTo: '',
                    searchAmount: null,
                    searchResults: [],
                    loading: false,
                    searchedOnce: false,
                    bankAccountId: {{ $bankAccount->id }},

                    openMatchModal(id, date, description, amount) {
                        this.stagedTransactionId = id;
                        this.stagedDate = date;
                        this.stagedDescription = description;
                        this.stagedAmount = parseFloat(amount);
                        this.searchTerm = description.substring(0, 50);
                        this.searchAmount = parseFloat(amount);
                        
                        const originalStagedDateObj = new Date(date + 'T00:00:00');
                        
                        const fromDate = new Date(new Date(originalStagedDateObj).setDate(originalStagedDateObj.getDate() - 7));
                        this.searchDateFrom = fromDate.toISOString().split('T')[0];
                        
                        const toDate = new Date(new Date(originalStagedDateObj).setDate(originalStagedDateObj.getDate() + 7));
                        this.searchDateTo = toDate.toISOString().split('T')[0];
                        
                        this.searchResults = [];
                        this.searchedOnce = false;
                        this.showModal = true;
                    },
                    closeModal() {
                        this.showModal = false;
                    },
                    async performSearch() {
                        this.loading = true;
                        this.searchedOnce = true;
                        this.searchResults = [];
                        const params = new URLSearchParams({
                            search_term: this.searchTerm,
                            search_date_from: this.searchDateFrom,
                            search_date_to: this.searchDateTo,
                            staged_transaction_id: this.stagedTransactionId
                        });
                        if (this.searchAmount !== null && !isNaN(this.searchAmount)) {
                            const leeway = 0.05;
                            params.append('search_amount_min', (this.searchAmount - leeway).toFixed(2));
                            params.append('search_amount_max', (this.searchAmount + leeway).toFixed(2));
                        }
                        
                        const response = await fetch(`/bank-accounts/${this.bankAccountId}/transactions/search?${params.toString()}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            }
                        });
                        if (response.ok) {
                            this.searchResults = await response.json();
                        } else {
                            alert('Error searching transactions. Please try again.');
                            console.error('Search error:', await response.text());
                        }
                        this.loading = false;
                    },
                    async selectMatch(transactionId) {
                        if (!confirm('Are you sure you want to match this staged transaction to the selected existing transaction?')) {
                            return;
                        }
                        this.loading = true;
                        const csrfToken = document.querySelector("meta[name='csrf-token']").getAttribute('content');
                        try {
                            const response = await fetch(`/staged-transactions/${this.stagedTransactionId}/manual-match/${transactionId}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json'
                                },
                            });
                            const responseData = await response.json();
                            if (response.ok && responseData.success) {
                                alert(responseData.success);
                                this.closeModal();
                                window.location.reload();
                            } else {
                                alert('Error: ' + (responseData.error || 'Could not match transaction.'));
                                console.error('Matching error:', responseData);
                            }
                        } catch (error) {
                            alert('A network error occurred. Please try again.');
                            console.error('Network error during matching:', error);
                        }
                        this.loading = false;
                    }
                };
            });
        });
    </script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200" x-data="manualMatchComponent">
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
                                        <tr id="staged-transaction-row-{{ $st->id }}" class="{{ $st->status === 'potential_duplicate' ? 'bg-orange-50 hover:bg-orange-100' : 'hover:bg-gray-50' }}">
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
                                                        <button type="submit" class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 transition ease-in-out duration-150">
                                                            Approve
                                                        </button>
                                                    </form>

                                                    @if($st->status === 'potential_duplicate')
                                                    <form method="POST" action="{{ route('staged-transactions.unmatch', ['stagedTransaction' => $st, 'page' => $stagedTransactions->currentPage()]) }}" class="inline-block mr-1">
                                                        @csrf
                                                        <button type="submit" class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                                            Not a Duplicate
                                                        </button>
                                                    </form>
                                                    @endif

                                                    <form method="POST" action="{{ route('staged-transactions.ignore', ['stagedTransaction' => $st, 'page' => $stagedTransactions->currentPage()]) }}" class="inline-block ml-1">
                                                        @csrf
                                                        <button type="submit" class="text-xs px-2 py-1 {{ $st->status === 'potential_duplicate' ? 'bg-orange-500 hover:bg-orange-600 focus:ring-orange-500' : 'bg-red-500 hover:bg-red-600 focus:ring-red-500' }} text-white rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-opacity-50" onclick="return confirm('Are you sure you want to ignore this transaction?');">
                                                            Ignore
                                                        </button>
                                                    </form>

                                                    @if($st->status === 'pending_review' || $st->status === 'potential_duplicate')
                                                    <button @click="openMatchModal({{ $st->id }}, '{{ $st->transaction_date->format('Y-m-d') }}', '{{ $st->description }}', {{ $st->amount }})" class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 transition ease-in-out duration-150 ml-1" dusk="find-and-match-button-{{ $st->id }}">
                                                        Find & Match
                                                    </button>
                                                    @endif

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

                        <!-- Manual Match Modal -->
                        <div x-show="showModal" @keydown.escape.window="closeModal()" style="display: none;" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" dusk="manual-match-modal">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()" aria-hidden="true"></div>
                        
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        
                                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    Manually Match Staged Transaction <span x-text="stagedTransactionId"></span>
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-600 mb-1">Staged: <span x-text="stagedDate"></span> | <span x-text="stagedDescription"></span> | <span x-text="stagedAmount.toFixed(2)"></span></p>
                                                    <!-- Search Form -->
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                        <div>
                                                            <label for="search_term" class="block text-sm font-medium text-gray-700">Description</label>
                                                            <input type="text" x-model="searchTerm" id="search_term" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" dusk="search_description">
                                                        </div>
                                                        <div>
                                                            <label for="search_amount" class="block text-sm font-medium text-gray-700">Amount (+/- 0.05)</label>
                                                            <input type="number" step="0.01" x-model.number="searchAmount" id="search_amount" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" dusk="search_amount">
                                                        </div>
                                                        <div>
                                                            <label for="search_date_from" class="block text-sm font-medium text-gray-700">Date From (approx. 7 days prior)</label>
                                                            <input type="date" x-model="searchDateFrom" id="search_date_from" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" dusk="search_date_from">
                                                        </div>
                                                        <div>
                                                            <label for="search_date_to" class="block text-sm font-medium text-gray-700">Date To (approx. 7 days after)</label>
                                                            <input type="date" x-model="searchDateTo" id="search_date_to" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" dusk="search_date_to">
                                                        </div>
                                                    </div>
                                                    <button @click="performSearch()" class="mb-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" dusk="modal-search-button">
                                                        Search Existing Transactions
                                                    </button>

                                                    <!-- Search Results -->
                                                    <div x-show="loading" class="text-center"><p>Loading...</p></div>
                                                    <div x-show="!loading && searchResults.length === 0 && searchedOnce" class="text-center"><p>No matching transactions found.</p></div>
                                                    <ul x-show="!loading && searchResults.length > 0" class="max-h-60 overflow-y-auto divide-y divide-gray-200 border rounded-md">
                                                        <template x-for="transaction in searchResults" :key="transaction.id">
                                                            <li :id="'search-result-' + transaction.id" class="p-3 hover:bg-gray-50 flex justify-between items-center">
                                                                <div>
                                                                    <p class="text-sm font-medium text-gray-900"><span x-text="transaction.transaction_date"></span> - <span x-text="transaction.description"></span></p>
                                                                    <p class="text-sm text-gray-500">Amount: <span x-text="transaction.amount.toFixed(2)"></span> | Cat: <span x-text="transaction.category ? transaction.category.name : 'N/A'"></span></p>
                                                                </div>
                                                                <button @click="selectMatch(transaction.id)" class="ml-2 text-xs px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600" :dusk="'select-match-button-' + transaction.id">Select Match</button>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button @click="closeModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
