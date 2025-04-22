<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Income Analysis') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('analytics.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Analytics Dashboard') }}
                </a>
                <a href="{{ route('analytics.expenses') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Expense Breakdown') }}
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
                        <a href="{{ route('analytics.income', ['period' => 'week']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'week' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Week') }}
                        </a>
                        <a href="{{ route('analytics.income', ['period' => 'month']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Month') }}
                        </a>
                        <a href="{{ route('analytics.income', ['period' => 'quarter']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'quarter' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Quarter') }}
                        </a>
                        <a href="{{ route('analytics.income', ['period' => 'year']) }}" class="px-4 py-2 rounded-md text-sm font-medium {{ $period === 'year' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('Year') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Income Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Income Summary') }}</h3>
                <div class="text-sm text-gray-600 mb-4">
                    {{ __('Period') }}: {{ date('M d, Y', strtotime($incomeAnalysis['dateRange']['start'])) }} - {{ date('M d, Y', strtotime($incomeAnalysis['dateRange']['end'])) }}
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Income -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-600 mb-1">{{ __('Total Income') }}</h4>
                        <p class="text-2xl font-semibold text-green-600">
                            ${{ number_format(array_sum(array_column($incomeAnalysis['incomeByCategory'], 'amount')), 2) }}
                        </p>
                    </div>

                    <!-- Income Sources -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-600 mb-1">{{ __('Income Sources') }}</h4>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ count($incomeAnalysis['incomeByCategory']) }}
                        </p>
                    </div>

                    <!-- Average Per Source -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-600 mb-1">{{ __('Average Per Source') }}</h4>
                        <p class="text-2xl font-semibold text-gray-900">
                            ${{ number_format(count($incomeAnalysis['incomeByCategory']) > 0 ? array_sum(array_column($incomeAnalysis['incomeByCategory'], 'amount')) / count($incomeAnalysis['incomeByCategory']) : 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Income by Category -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Income by Category') }}</h3>
                    <canvas id="incomeByCategoryChart" height="300"></canvas>
                </div>

                <!-- Income Trends -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Income Trends') }}</h3>
                    <canvas id="incomeTrendsChart" height="300"></canvas>
                </div>
            </div>

            <!-- Income Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Income Details') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Category') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Amount') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Percentage') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($incomeAnalysis['incomeByCategory'] as $category)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-4 w-4 rounded-full mr-2" style="background-color: {{ $category['color'] }}"></div>
                                            <div class="text-sm font-medium text-gray-900">{{ $category['name'] }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-medium">
                                        ${{ number_format($category['amount'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                        {{ number_format($category['percentage'], 2) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('analytics.index', ['period' => $period, 'category' => $category['id']]) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ __('View Details') }}
                                        </a>
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
            // Income by Category Chart
            const incomeByCategoryCtx = document.getElementById('incomeByCategoryChart').getContext('2d');
            const incomeByCategoryChart = new Chart(incomeByCategoryCtx, {
                type: 'pie',
                data: {
                    labels: @json(array_map(function($category) { return $category['name']; }, $incomeAnalysis['incomeByCategory'])),
                    datasets: [{
                        data: @json(array_map(function($category) { return $category['amount']; }, $incomeAnalysis['incomeByCategory'])),
                        backgroundColor: @json(array_map(function($category) { return $category['color']; }, $incomeAnalysis['incomeByCategory'])),
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

            // Income Trends Chart
            const incomeTrendsCtx = document.getElementById('incomeTrendsChart').getContext('2d');
            const incomeTrendsChart = new Chart(incomeTrendsCtx, {
                type: 'line',
                data: @json($incomeAnalysis['incomeTrends']),
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
        });
    </script>
    @endpush
</x-app-layout>
