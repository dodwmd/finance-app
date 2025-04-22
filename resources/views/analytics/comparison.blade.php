<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Period Comparison') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('analytics.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Analytics Dashboard') }}
                </a>
                <a href="{{ route('analytics.expenses') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Expense Breakdown') }}
                </a>
                <a href="{{ route('analytics.income') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Income Analysis') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Period and Comparison selectors -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Select Period') }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('analytics.comparison', ['period' => 'month', 'compare' => $compareWith]) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                {{ __('Month') }}
                            </a>
                            <a href="{{ route('analytics.comparison', ['period' => 'quarter', 'compare' => $compareWith]) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'quarter' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                {{ __('Quarter') }}
                            </a>
                            <a href="{{ route('analytics.comparison', ['period' => 'year', 'compare' => $compareWith]) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'year' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                {{ __('Year') }}
                            </a>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Compare With') }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('analytics.comparison', ['period' => $period, 'compare' => 'previous']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $compareWith === 'previous' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                {{ __('Previous Period') }}
                            </a>
                            <a href="{{ route('analytics.comparison', ['period' => $period, 'compare' => 'last-year']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $compareWith === 'last-year' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                {{ __('Same Period Last Year') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Period Date Ranges -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Current Period') }}</h3>
                        <p class="text-gray-600">
                            {{ date('M d, Y', strtotime($comparisonData['current']['dateRange']['start'])) }} - 
                            {{ date('M d, Y', strtotime($comparisonData['current']['dateRange']['end'])) }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Comparison Period') }}</h3>
                        <p class="text-gray-600">
                            {{ date('M d, Y', strtotime($comparisonData['comparison']['dateRange']['start'])) }} - 
                            {{ date('M d, Y', strtotime($comparisonData['comparison']['dateRange']['end'])) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Financial Comparison -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Income Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Income') }}</p>
                                <p class="text-2xl font-semibold text-green-600">${{ number_format($comparisonData['current']['income'], 2) }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Previous') }}: ${{ number_format($comparisonData['comparison']['income'], 2) }}</p>
                            </div>
                            <div class="{{ $comparisonData['percentChanges']['income'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <span class="text-lg font-medium">
                                    {{ $comparisonData['percentChanges']['income'] >= 0 ? '+' : '' }}{{ number_format($comparisonData['percentChanges']['income'], 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expenses Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Expenses') }}</p>
                                <p class="text-2xl font-semibold text-red-600">${{ number_format($comparisonData['current']['expenses'], 2) }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Previous') }}: ${{ number_format($comparisonData['comparison']['expenses'], 2) }}</p>
                            </div>
                            <div class="{{ $comparisonData['percentChanges']['expenses'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <span class="text-lg font-medium">
                                    {{ $comparisonData['percentChanges']['expenses'] >= 0 ? '+' : '' }}{{ number_format($comparisonData['percentChanges']['expenses'], 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Balance') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">${{ number_format($comparisonData['current']['balance'], 2) }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Previous') }}: ${{ number_format($comparisonData['comparison']['balance'], 2) }}</p>
                            </div>
                            <div class="{{ $comparisonData['percentChanges']['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <span class="text-lg font-medium">
                                    {{ $comparisonData['percentChanges']['balance'] >= 0 ? '+' : '' }}{{ number_format($comparisonData['percentChanges']['balance'], 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Savings Rate Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Savings Rate') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($comparisonData['current']['savingsRate'], 2) }}%</p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Previous') }}: {{ number_format($comparisonData['comparison']['savingsRate'], 2) }}%</p>
                            </div>
                            <div class="{{ $comparisonData['percentChanges']['savingsRate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <span class="text-lg font-medium">
                                    {{ $comparisonData['percentChanges']['savingsRate'] >= 0 ? '+' : '' }}{{ number_format($comparisonData['percentChanges']['savingsRate'], 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Income vs Expenses Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Income vs Expenses') }}</h3>
                    <canvas id="incomeExpensesComparisonChart" height="300"></canvas>
                </div>

                <!-- Category Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Expense Category Comparison') }}</h3>
                    <canvas id="categoryComparisonChart" height="300"></canvas>
                </div>
            </div>

            <!-- Detailed Comparison Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Expense Category Comparison') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Category') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Current Period') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Comparison Period') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Change') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                // Get all unique category ids
                                $allCategoryIds = [];
                                foreach ($comparisonData['current']['expenseBreakdown'] as $category) {
                                    $allCategoryIds[$category['id']] = true;
                                }
                                foreach ($comparisonData['comparison']['expenseBreakdown'] as $category) {
                                    $allCategoryIds[$category['id']] = true;
                                }
                                
                                // Prepare comparison data
                                $categoryComparison = [];
                                foreach (array_keys($allCategoryIds) as $categoryId) {
                                    $currentCategory = collect($comparisonData['current']['expenseBreakdown'])->firstWhere('id', $categoryId);
                                    $comparisonCategory = collect($comparisonData['comparison']['expenseBreakdown'])->firstWhere('id', $categoryId);
                                    
                                    // Use current category details, or comparison category if current doesn't exist
                                    $categoryDetails = $currentCategory ?? $comparisonCategory;
                                    
                                    $currentAmount = $currentCategory ? $currentCategory['amount'] : 0;
                                    $comparisonAmount = $comparisonCategory ? $comparisonCategory['amount'] : 0;
                                    
                                    $percentChange = $comparisonAmount > 0 
                                        ? (($currentAmount - $comparisonAmount) / $comparisonAmount) * 100 
                                        : ($currentAmount > 0 ? 100 : 0);
                                    
                                    $categoryComparison[] = [
                                        'id' => $categoryId,
                                        'name' => $categoryDetails['name'],
                                        'color' => $categoryDetails['color'],
                                        'currentAmount' => $currentAmount,
                                        'comparisonAmount' => $comparisonAmount,
                                        'percentChange' => $percentChange,
                                    ];
                                }
                                
                                // Sort by current amount descending
                                usort($categoryComparison, function($a, $b) {
                                    return $b['currentAmount'] <=> $a['currentAmount'];
                                });
                            @endphp
                            
                            @foreach($categoryComparison as $category)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-4 w-4 rounded-full mr-2" style="background-color: {{ $category['color'] }}"></div>
                                            <div class="text-sm font-medium text-gray-900">{{ $category['name'] }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-medium">
                                        ${{ number_format($category['currentAmount'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        ${{ number_format($category['comparisonAmount'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $category['percentChange'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $category['percentChange'] >= 0 ? '+' : '' }}{{ number_format($category['percentChange'], 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Income vs Expenses Comparison Chart
            const incomeExpensesCtx = document.getElementById('incomeExpensesComparisonChart').getContext('2d');
            
            const incomeExpensesComparisonChart = new Chart(incomeExpensesCtx, {
                type: 'bar',
                data: {
                    labels: ['Income', 'Expenses', 'Balance'],
                    datasets: [
                        {
                            label: 'Current Period',
                            data: [
                                {{ $comparisonData['current']['income'] }},
                                {{ $comparisonData['current']['expenses'] }},
                                {{ $comparisonData['current']['balance'] }}
                            ],
                            backgroundColor: [
                                'rgba(56, 161, 105, 0.7)',
                                'rgba(229, 62, 62, 0.7)',
                                'rgba(66, 153, 225, 0.7)'
                            ],
                            borderColor: [
                                'rgb(56, 161, 105)',
                                'rgb(229, 62, 62)',
                                'rgb(66, 153, 225)'
                            ],
                            borderWidth: 1
                        },
                        {
                            label: 'Comparison Period',
                            data: [
                                {{ $comparisonData['comparison']['income'] }},
                                {{ $comparisonData['comparison']['expenses'] }},
                                {{ $comparisonData['comparison']['balance'] }}
                            ],
                            backgroundColor: [
                                'rgba(56, 161, 105, 0.3)',
                                'rgba(229, 62, 62, 0.3)',
                                'rgba(66, 153, 225, 0.3)'
                            ],
                            borderColor: [
                                'rgb(56, 161, 105)',
                                'rgb(229, 62, 62)',
                                'rgb(66, 153, 225)'
                            ],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': $' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Category Comparison Chart
            const categoryComparisonCtx = document.getElementById('categoryComparisonChart').getContext('2d');
            
            // Prepare data for the chart
            const categoryLabels = @json(array_map(function($cat) { return $cat['name']; }, $categoryComparison));
            const currentAmounts = @json(array_map(function($cat) { return $cat['currentAmount']; }, $categoryComparison));
            const comparisonAmounts = @json(array_map(function($cat) { return $cat['comparisonAmount']; }, $categoryComparison));
            const categoryColors = @json(array_map(function($cat) { return $cat['color']; }, $categoryComparison));
            
            const categoryComparisonChart = new Chart(categoryComparisonCtx, {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [
                        {
                            label: 'Current Period',
                            data: currentAmounts,
                            backgroundColor: categoryColors.map(color => color + '99'), // Add transparency
                            borderColor: categoryColors,
                            borderWidth: 1
                        },
                        {
                            label: 'Comparison Period',
                            data: comparisonAmounts,
                            backgroundColor: categoryColors.map(color => color + '44'), // More transparency
                            borderColor: categoryColors,
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': $' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
