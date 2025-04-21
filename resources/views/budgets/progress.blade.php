<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Budget Progress') }}: {{ $budget->name }}
            </h2>
            <div>
                <a href="{{ route('budgets.show', $budget) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                    {{ __('Budget Details') }}
                </a>
                <a href="{{ route('budgets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to Budgets') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Budget Progress Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="border rounded-lg p-4 bg-blue-50">
                            <h3 class="text-sm text-gray-500 mb-1">Budget</h3>
                            <p class="text-2xl font-bold text-blue-700">${{ number_format($budget->amount, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ ucfirst($budget->period) }} budget for {{ $budget->category->name }}</p>
                        </div>
                        
                        <div class="border rounded-lg p-4 bg-red-50">
                            <h3 class="text-sm text-gray-500 mb-1">Spent</h3>
                            <p class="text-2xl font-bold text-red-700">${{ number_format($budgetProgress['spent'], 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Total spent during this period</p>
                        </div>
                        
                        <div class="border rounded-lg p-4 {{ $budgetProgress['remaining'] < 0 ? 'bg-red-50' : 'bg-green-50' }}">
                            <h3 class="text-sm text-gray-500 mb-1">Remaining</h3>
                            <p class="text-2xl font-bold {{ $budgetProgress['remaining'] < 0 ? 'text-red-700' : 'text-green-700' }}">
                                ${{ number_format($budgetProgress['remaining'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Amount left for this period</p>
                        </div>
                        
                        <div class="border rounded-lg p-4 {{ $budgetProgress['percentage'] >= 100 ? 'bg-red-50' : ($budgetProgress['percentage'] >= 80 ? 'bg-yellow-50' : 'bg-blue-50') }}">
                            <h3 class="text-sm text-gray-500 mb-1">Progress</h3>
                            <p class="text-2xl font-bold {{ $budgetProgress['percentage'] >= 100 ? 'text-red-700' : ($budgetProgress['percentage'] >= 80 ? 'text-yellow-700' : 'text-blue-700') }}">
                                {{ number_format($budgetProgress['percentage'], 1) }}%
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Percentage of budget used</p>
                        </div>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                        <div class="h-4 rounded-full {{ $budgetProgress['is_exceeded'] ? 'bg-red-600' : ($budgetProgress['percentage'] >= 80 ? 'bg-yellow-400' : 'bg-blue-600') }}" 
                             style="width: {{ min($budgetProgress['percentage'], 100) }}%"></div>
                    </div>
                    
                    <div class="flex justify-between items-center text-sm text-gray-600">
                        <div>
                            <span class="font-medium">Period:</span> 
                            {{ \Carbon\Carbon::parse($budgetProgress['start_date'])->format('M d, Y') }} - 
                            {{ \Carbon\Carbon::parse($budgetProgress['end_date'])->format('M d, Y') }}
                        </div>
                        <div>
                            <span class="font-medium">Category:</span> 
                            {{ $budget->category->name }}
                        </div>
                        <div>
                            <span class="font-medium">Status:</span>
                            @if($budgetProgress['is_exceeded'])
                                <span class="text-red-600 font-semibold">Exceeded</span>
                            @elseif($budgetProgress['percentage'] >= 80)
                                <span class="text-yellow-600 font-semibold">Near Limit</span>
                            @else
                                <span class="text-green-600 font-semibold">On Track</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Budget Analytics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Spending Breakdown</h3>
                    
                    <div class="h-80">
                        <!-- Placeholder for Chart.js chart - we'll implement with JavaScript -->
                        <canvas id="dailySpendingChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Transactions for this Budget's Category -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Transactions ({{ $transactions->count() }})</h3>
                    
                    @if($transactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $transaction->transaction_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $transaction->type === 'income' ? 'bg-green-100 text-green-800' : 
                                                        ($transaction->type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                                    {{ ucfirst($transaction->type) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                                                {{ $transaction->type === 'income' ? 'text-green-600' : 
                                                    ($transaction->type === 'expense' ? 'text-red-600' : 'text-blue-600') }}">
                                                {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}
                                                ${{ number_format($transaction->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('transactions.edit', $transaction) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No transactions found for this category during the budget period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const transactions = @json($transactions);
            const startDate = new Date('{{ $budgetProgress['start_date'] }}');
            const endDate = new Date('{{ $budgetProgress['end_date'] }}');
            
            // Create an object to store daily spending
            const dailySpending = {};
            
            // Initialize all dates in the range with zero
            let currentDate = new Date(startDate);
            while (currentDate <= endDate) {
                const dateStr = currentDate.toISOString().split('T')[0];
                dailySpending[dateStr] = 0;
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            // Fill in the actual spending data
            transactions.forEach(transaction => {
                const dateStr = transaction.transaction_date.split('T')[0];
                if (transaction.type === 'expense') {
                    dailySpending[dateStr] = (dailySpending[dateStr] || 0) + parseFloat(transaction.amount);
                }
            });
            
            // Extract labels and data for the chart
            const labels = Object.keys(dailySpending).sort();
            const data = labels.map(date => dailySpending[date]);
            
            // Create the chart
            const ctx = document.getElementById('dailySpendingChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.map(date => {
                        const d = new Date(date);
                        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Daily Spending',
                        data: data,
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Amount ($)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.raw.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
