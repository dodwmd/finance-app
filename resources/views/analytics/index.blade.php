<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Expense Analytics') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('analytics.expenses') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Expense Breakdown') }}
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
                        <a href="{{ route('analytics.index', ['period' => 'week']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'week' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Week') }}
                        </a>
                        <a href="{{ route('analytics.index', ['period' => 'month']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Month') }}
                        </a>
                        <a href="{{ route('analytics.index', ['period' => 'quarter']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'quarter' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Quarter') }}
                        </a>
                        <a href="{{ route('analytics.index', ['period' => 'year']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'year' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Year') }}
                        </a>
                    </div>
                    @if(!empty($categories))
                    <div class="ml-4">
                        <select id="category-filter" onchange="location = this.value;" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="{{ route('analytics.index', ['period' => $period]) }}" {{ $categoryId === null ? 'selected' : '' }}>{{ __('All Categories') }}</option>
                            @foreach($categories as $category)
                            <option value="{{ route('analytics.index', ['period' => $period, 'category' => $category->id]) }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Current Balance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Current Balance') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">${{ number_format($analyticsData['summary']['balance'], 2) }}</p>
                            </div>
                            <div class="{{ $analyticsData['periodComparison']['changes']['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <span class="text-sm font-medium">
                                    {{ $analyticsData['periodComparison']['changes']['balance'] >= 0 ? '+' : '' }}{{ number_format($analyticsData['periodComparison']['changes']['balance'], 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Income -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Income') }}</p>
                                <p class="text-2xl font-semibold text-green-600">${{ number_format($analyticsData['summary']['income'], 2) }}</p>
                            </div>
                            <div class="{{ $analyticsData['periodComparison']['changes']['income'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <span class="text-sm font-medium">
                                    {{ $analyticsData['periodComparison']['changes']['income'] >= 0 ? '+' : '' }}{{ number_format($analyticsData['periodComparison']['changes']['income'], 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expenses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Expenses') }}</p>
                                <p class="text-2xl font-semibold text-red-600">${{ number_format($analyticsData['summary']['expenses'], 2) }}</p>
                            </div>
                            <div class="{{ $analyticsData['periodComparison']['changes']['expenses'] <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                <span class="text-sm font-medium">
                                    {{ $analyticsData['periodComparison']['changes']['expenses'] >= 0 ? '+' : '' }}{{ number_format($analyticsData['periodComparison']['changes']['expenses'], 2) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Savings Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ __('Savings Rate') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($analyticsData['summary']['savingsRate'], 2) }}%</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('Target: 20%') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Daily Trends -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Daily Trends') }}</h3>
                    <canvas id="dailyTrendsChart" height="300"></canvas>
                </div>

                <!-- Expense Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Expense Breakdown') }}</h3>
                    <canvas id="expenseBreakdownChart" height="300"></canvas>
                </div>
            </div>

            <!-- Period Comparison -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Period Comparison') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Previous Period -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-2">
                            {{ __('Previous') }} {{ ucfirst($period) }}
                        </h4>
                        <div class="bg-gray-100 rounded-lg p-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-sm text-gray-600">{{ __('Income') }}</span>
                                <span class="text-sm font-medium">${{ number_format($analyticsData['periodComparison']['previous']['income'], 2) }}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm text-gray-600">{{ __('Expenses') }}</span>
                                <span class="text-sm font-medium">${{ number_format($analyticsData['periodComparison']['previous']['expenses'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('Balance') }}</span>
                                <span class="text-sm font-medium">${{ number_format($analyticsData['periodComparison']['previous']['balance'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Arrows -->
                    <div class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>

                    <!-- Current Period -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 mb-2">
                            {{ __('Current') }} {{ ucfirst($period) }}
                        </h4>
                        <div class="bg-gray-100 rounded-lg p-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-sm text-gray-600">{{ __('Income') }}</span>
                                <span class="text-sm font-medium">${{ number_format($analyticsData['summary']['income'], 2) }}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm text-gray-600">{{ __('Expenses') }}</span>
                                <span class="text-sm font-medium">${{ number_format($analyticsData['summary']['expenses'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('Balance') }}</span>
                                <span class="text-sm font-medium">${{ number_format($analyticsData['summary']['balance'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Daily Trends Chart
            const dailyTrendsCtx = document.getElementById('dailyTrendsChart').getContext('2d');
            const dailyTrendsChart = new Chart(dailyTrendsCtx, {
                type: 'line',
                data: {
                    labels: @json($analyticsData['dailyTrends']['labels']),
                    datasets: @json($analyticsData['dailyTrends']['datasets'])
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
                        }
                    }
                }
            });

            // Expense Breakdown Chart
            const expenseBreakdownCtx = document.getElementById('expenseBreakdownChart').getContext('2d');
            const expenseBreakdownChart = new Chart(expenseBreakdownCtx, {
                type: 'pie',
                data: {
                    labels: @json(array_map(function($category) { return $category['name']; }, $analyticsData['expenseBreakdown'])),
                    datasets: [{
                        data: @json(array_map(function($category) { return $category['amount']; }, $analyticsData['expenseBreakdown'])),
                        backgroundColor: @json(array_map(function($category) { return $category['color']; }, $analyticsData['expenseBreakdown'])),
                    }]
                },
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
