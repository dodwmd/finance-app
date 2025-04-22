<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Expense Breakdown') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('analytics.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Analytics Dashboard') }}
                </a>
                <a href="{{ route('analytics.income') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Income Analysis') }}
                </a>
                <a href="{{ route('analytics.comparison') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Period Comparison') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Period selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Select Period') }}</h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('analytics.expenses', ['period' => 'week']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'week' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Week') }}
                        </a>
                        <a href="{{ route('analytics.expenses', ['period' => 'month']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Month') }}
                        </a>
                        <a href="{{ route('analytics.expenses', ['period' => 'quarter']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'quarter' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Quarter') }}
                        </a>
                        <a href="{{ route('analytics.expenses', ['period' => 'year']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'year' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Year') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Category Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Category Comparison') }}</h3>
                    <div class="mb-4">
                        <div class="text-sm text-gray-600 mb-2">
                            {{ __('Current Period') }}: {{ date('M d, Y', strtotime($categoryComparison['dateRange']['start'])) }} - {{ date('M d, Y', strtotime($categoryComparison['dateRange']['end'])) }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ __('Previous Period') }}: {{ date('M d, Y', strtotime($categoryComparison['previousDateRange']['start'])) }} - {{ date('M d, Y', strtotime($categoryComparison['previousDateRange']['end'])) }}
                        </div>
                    </div>
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Category') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Current') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Previous') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Change') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($categoryComparison['current'] as $currentCategory)
                                    @php
                                        $previousCategory = collect($categoryComparison['previous'])->firstWhere('id', $currentCategory['id']);
                                        $previousAmount = $previousCategory ? $previousCategory['amount'] : 0;
                                        $changePercentage = $previousAmount > 0 
                                            ? (($currentCategory['amount'] - $previousAmount) / $previousAmount) * 100 
                                            : ($currentCategory['amount'] > 0 ? 100 : 0);
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-4 w-4 rounded-full mr-2" style="background-color: {{ $currentCategory['color'] }}"></div>
                                                <div class="text-sm font-medium text-gray-900">{{ $currentCategory['name'] }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                            ${{ number_format($currentCategory['amount'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                            ${{ number_format($previousAmount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $changePercentage <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $changePercentage >= 0 ? '+' : '' }}{{ number_format($changePercentage, 2) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <canvas id="categoryComparisonChart" height="300"></canvas>
                </div>

                <!-- Expense Trends -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Expense Trends') }}</h3>
                    <canvas id="expenseTrendsChart" height="300"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Spending by Day of Week -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Spending by Day of Week') }}</h3>
                    <canvas id="weekdayAnalysisChart" height="300"></canvas>
                </div>

                <!-- Spending by Time of Day -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Spending by Time of Day') }}</h3>
                    <canvas id="timeOfDayAnalysisChart" height="300"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Largest Expenses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Largest Expenses') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Description') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Category') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Date') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Amount') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($spendingPatterns['largestExpenses'] as $expense)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $expense['description'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $expense['category'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ date('M d, Y', strtotime($expense['date'])) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600 font-medium">
                                            ${{ number_format($expense['amount'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Most Frequent Vendors -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Most Frequent Vendors') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Vendor') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('# of Transactions') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Total Amount') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Average') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($spendingPatterns['frequentVendors'] as $vendor)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $vendor['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                            {{ $vendor['count'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600 font-medium">
                                            ${{ number_format($vendor['total'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                            ${{ number_format($vendor['total'] / $vendor['count'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Expense Trends Chart
            const expenseTrendsCtx = document.getElementById('expenseTrendsChart').getContext('2d');
            const expenseTrendsChart = new Chart(expenseTrendsCtx, {
                type: 'line',
                data: {
                    labels: @json($expenseTrends['dailyTrends']['labels']),
                    datasets: [{
                        label: 'Daily Expenses',
                        data: @json($expenseTrends['dailyTrends']['datasets'][0]['data']),
                        borderColor: '#e53e3e',
                        backgroundColor: 'rgba(229, 62, 62, 0.1)',
                        tension: 0.3
                    }]
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
                                    return '$' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Category Comparison Chart
            const categoryComparisonCtx = document.getElementById('categoryComparisonChart').getContext('2d');
            
            // Extract current and previous data for the chart
            const currentCategories = @json(array_map(function($category) { 
                return [
                    'name' => $category['name'],
                    'amount' => $category['amount'],
                    'color' => $category['color']
                ]; 
            }, $categoryComparison['current']));
            
            const previousCategories = @json(array_map(function($category) { 
                return [
                    'name' => $category['name'],
                    'amount' => $category['amount'],
                    'color' => $category['color']
                ]; 
            }, $categoryComparison['previous']));
            
            // Create a mapping of category ID to colors
            const colorMap = {};
            currentCategories.forEach(cat => {
                colorMap[cat.name] = cat.color;
            });
            
            // Get all unique category names
            const allCategoryNames = [...new Set([
                ...currentCategories.map(c => c.name),
                ...previousCategories.map(c => c.name)
            ])];
            
            // Prepare data for the chart
            const currentAmounts = [];
            const previousAmounts = [];
            const backgroundColors = [];
            
            allCategoryNames.forEach(catName => {
                const currentCat = currentCategories.find(c => c.name === catName);
                const previousCat = previousCategories.find(c => c.name === catName);
                
                currentAmounts.push(currentCat ? currentCat.amount : 0);
                previousAmounts.push(previousCat ? previousCat.amount : 0);
                backgroundColors.push(colorMap[catName] || '#607D8B');
            });
            
            const categoryComparisonChart = new Chart(categoryComparisonCtx, {
                type: 'bar',
                data: {
                    labels: allCategoryNames,
                    datasets: [
                        {
                            label: 'Current Period',
                            data: currentAmounts,
                            backgroundColor: backgroundColors.map(color => color + '99'), // Add transparency
                            borderColor: backgroundColors,
                            borderWidth: 1
                        },
                        {
                            label: 'Previous Period',
                            data: previousAmounts,
                            backgroundColor: backgroundColors.map(color => color + '44'), // More transparency
                            borderColor: backgroundColors,
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

            // Weekday Analysis Chart
            const weekdayAnalysisCtx = document.getElementById('weekdayAnalysisChart').getContext('2d');
            const weekdayAnalysisChart = new Chart(weekdayAnalysisCtx, {
                type: 'bar',
                data: @json($expenseTrends['weekdayAnalysis']),
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
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Time of Day Analysis Chart
            const timeOfDayAnalysisCtx = document.getElementById('timeOfDayAnalysisChart').getContext('2d');
            const timeOfDayAnalysisChart = new Chart(timeOfDayAnalysisCtx, {
                type: 'pie',
                data: @json($expenseTrends['timeOfDayAnalysis']),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const dataset = context.dataset.data;
                                    const total = dataset.reduce((acc, data) => acc + data, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: $${value.toLocaleString()} (${percentage}%)`;
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
