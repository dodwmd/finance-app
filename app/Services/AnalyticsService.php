<?php

namespace App\Services;

use App\Contracts\Repositories\TransactionRepositoryInterface;
use App\Models\Category;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * The transaction repository instance.
     */
    protected $transactionRepository;

    /**
     * Create a new service instance.
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Get main analytics data based on period.
     */
    public function getAnalyticsData(int $userId, string $period = 'month', ?int $categoryId = null): array
    {
        $dateRange = $this->getDateRangeFromPeriod($period);
        $transactions = $this->transactionRepository->getByDateRange(
            $userId,
            $dateRange['start'],
            $dateRange['end']
        );

        if ($categoryId) {
            $transactions = $transactions->where('category_id', $categoryId);
        }

        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');
        $balance = $income - $expenses;

        return [
            'summary' => [
                'income' => $income,
                'expenses' => $expenses,
                'balance' => $balance,
                'savingsRate' => $income > 0 ? round(($income - $expenses) / $income * 100, 2) : 0,
            ],
            'expenseBreakdown' => $this->getExpenseBreakdown($transactions),
            'dailyTrends' => $this->getDailyTrends($transactions, $dateRange),
            'periodComparison' => $this->getPeriodComparison($userId, $period, $categoryId),
            'period' => $period,
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Get expense trends data.
     */
    public function getExpenseTrends(int $userId, string $period = 'month'): array
    {
        $dateRange = $this->getDateRangeFromPeriod($period);
        $transactions = $this->transactionRepository->getByDateRange(
            $userId,
            $dateRange['start'],
            $dateRange['end']
        )->where('type', 'expense');

        return [
            'dailyTrends' => $this->getDailyTrends($transactions, $dateRange),
            'weekdayAnalysis' => $this->getWeekdayAnalysis($transactions),
            'timeOfDayAnalysis' => $this->getTimeOfDayAnalysis($transactions),
            'period' => $period,
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Get category comparison data.
     */
    public function getCategoryComparison(int $userId, string $period = 'month'): array
    {
        $dateRange = $this->getDateRangeFromPeriod($period);
        $transactions = $this->transactionRepository->getByDateRange(
            $userId,
            $dateRange['start'],
            $dateRange['end']
        )->where('type', 'expense');

        $previousDateRange = $this->getPreviousDateRange($period);
        $previousTransactions = $this->transactionRepository->getByDateRange(
            $userId,
            $previousDateRange['start'],
            $previousDateRange['end']
        )->where('type', 'expense');

        return [
            'current' => $this->getCategoriesWithTotals($transactions),
            'previous' => $this->getCategoriesWithTotals($previousTransactions),
            'period' => $period,
            'dateRange' => $dateRange,
            'previousDateRange' => $previousDateRange,
        ];
    }

    /**
     * Get spending patterns data.
     */
    public function getSpendingPatterns(int $userId, string $period = 'month'): array
    {
        $dateRange = $this->getDateRangeFromPeriod($period);
        $transactions = $this->transactionRepository->getByDateRange(
            $userId,
            $dateRange['start'],
            $dateRange['end']
        )->where('type', 'expense');

        return [
            'largestExpenses' => $this->getLargestExpenses($transactions),
            'frequentVendors' => $this->getFrequentVendors($transactions),
            'period' => $period,
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Get income analysis data.
     */
    public function getIncomeAnalysis(int $userId, string $period = 'month'): array
    {
        $dateRange = $this->getDateRangeFromPeriod($period);
        $transactions = $this->transactionRepository->getByDateRange(
            $userId,
            $dateRange['start'],
            $dateRange['end']
        )->where('type', 'income');

        return [
            'incomeByCategory' => $this->getIncomeByCategory($transactions),
            'incomeTrends' => $this->getIncomeTrends($userId, $period),
            'period' => $period,
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Get comparison data for comparing different periods.
     */
    public function getComparisonData(int $userId, string $period = 'year', string $compareWith = 'previous'): array
    {
        $dateRange = $this->getDateRangeFromPeriod($period);
        $comparisonDateRange = $this->getComparisonDateRange($period, $compareWith);

        $currentData = $this->getAnalyticsForPeriod($userId, $dateRange);
        $comparisonData = $this->getAnalyticsForPeriod($userId, $comparisonDateRange);

        return [
            'current' => $currentData,
            'comparison' => $comparisonData,
            'percentChanges' => $this->calculatePercentChanges($currentData, $comparisonData),
            'period' => $period,
            'compareWith' => $compareWith,
        ];
    }

    /**
     * Get available categories for the user.
     */
    public function getUserCategories(int $userId): Collection
    {
        return Category::where('user_id', $userId)
            ->orWhere('user_id', null)
            ->orderBy('name')
            ->get();
    }

    /**
     * Helper method to get date range from period.
     */
    protected function getDateRangeFromPeriod(string $period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'week':
                $start = $now->copy()->startOfWeek()->toDateString();
                $end = $now->copy()->endOfWeek()->toDateString();
                break;
            case 'month':
                $start = $now->copy()->startOfMonth()->toDateString();
                $end = $now->copy()->endOfMonth()->toDateString();
                break;
            case 'quarter':
                $start = $now->copy()->startOfQuarter()->toDateString();
                $end = $now->copy()->endOfQuarter()->toDateString();
                break;
            case 'year':
                $start = $now->copy()->startOfYear()->toDateString();
                $end = $now->copy()->endOfYear()->toDateString();
                break;
            case 'all':
                $start = '2000-01-01';
                $end = $now->toDateString();
                break;
            default:
                $start = $now->copy()->startOfMonth()->toDateString();
                $end = $now->copy()->endOfMonth()->toDateString();
        }

        return [
            'start' => $start,
            'end' => $end,
            'period' => $period,
        ];
    }

    /**
     * Get previous date range based on current period.
     */
    protected function getPreviousDateRange(string $period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'week':
                $start = $now->copy()->subWeek()->startOfWeek()->toDateString();
                $end = $now->copy()->subWeek()->endOfWeek()->toDateString();
                break;
            case 'month':
                $start = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $end = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'quarter':
                $start = $now->copy()->subQuarter()->startOfQuarter()->toDateString();
                $end = $now->copy()->subQuarter()->endOfQuarter()->toDateString();
                break;
            case 'year':
                $start = $now->copy()->subYear()->startOfYear()->toDateString();
                $end = $now->copy()->subYear()->endOfYear()->toDateString();
                break;
            default:
                $start = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $end = $now->copy()->subMonth()->endOfMonth()->toDateString();
        }

        return [
            'start' => $start,
            'end' => $end,
            'period' => $period,
        ];
    }

    /**
     * Get comparison date range based on period and comparison type.
     */
    protected function getComparisonDateRange(string $period, string $compareWith): array
    {
        if ($compareWith === 'previous') {
            return $this->getPreviousDateRange($period);
        }

        // For year-over-year or custom comparison, additional logic would go here
        return $this->getPreviousDateRange($period);
    }

    /**
     * Helper to get expense breakdown by category.
     */
    protected function getExpenseBreakdown(Collection $transactions): array
    {
        $expensesByCategory = [];
        $expenses = $transactions->where('type', 'expense');
        $totalExpenses = $expenses->sum('amount');

        foreach ($expenses as $expense) {
            $categoryId = $expense->category_id;
            $category = Category::find($categoryId);
            $categoryName = $category ? $category->name : 'Uncategorized';

            if (! isset($expensesByCategory[$categoryId])) {
                $expensesByCategory[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'color' => $category ? $category->color : '#607D8B',
                    'icon' => $category ? $category->icon : 'question-circle',
                    'amount' => 0,
                    'percentage' => 0,
                ];
            }

            $expensesByCategory[$categoryId]['amount'] += $expense->amount;
        }

        // Calculate percentages
        foreach ($expensesByCategory as &$category) {
            $category['percentage'] = $totalExpenses > 0
                ? round(($category['amount'] / $totalExpenses) * 100, 2)
                : 0;
        }

        // Sort by amount
        uasort($expensesByCategory, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return array_values($expensesByCategory);
    }

    /**
     * Helper to get daily transaction trends.
     */
    protected function getDailyTrends(Collection $transactions, array $dateRange): array
    {
        $startDate = Carbon::parse($dateRange['start']);
        $endDate = Carbon::parse($dateRange['end']);
        $daysInRange = $startDate->diffInDays($endDate) + 1;

        $dailyData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => [],
                    'borderColor' => '#e53e3e',
                    'backgroundColor' => 'rgba(229, 62, 62, 0.1)',
                ],
                [
                    'label' => 'Income',
                    'data' => [],
                    'borderColor' => '#38a169',
                    'backgroundColor' => 'rgba(56, 161, 105, 0.1)',
                ],
            ],
        ];

        // Initialize arrays with zeros
        for ($i = 0; $i < $daysInRange; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dailyData['labels'][] = $currentDate->format('M d');
            $dailyData['datasets'][0]['data'][] = 0; // Expenses
            $dailyData['datasets'][1]['data'][] = 0; // Income
        }

        // Fill in actual data
        foreach ($transactions as $transaction) {
            $transactionDate = Carbon::parse($transaction->date);
            $dayIndex = $transactionDate->diffInDays($startDate);

            if ($dayIndex >= 0 && $dayIndex < $daysInRange) {
                $datasetIndex = $transaction->type === 'expense' ? 0 : 1;
                $dailyData['datasets'][$datasetIndex]['data'][$dayIndex] += $transaction->amount;
            }
        }

        return $dailyData;
    }

    /**
     * Helper to get period comparison data.
     */
    protected function getPeriodComparison(int $userId, string $period, ?int $categoryId = null): array
    {
        $previousDateRange = $this->getPreviousDateRange($period);
        $currentDateRange = $this->getDateRangeFromPeriod($period);

        $previousTransactions = $this->transactionRepository->getByDateRange(
            $userId,
            $previousDateRange['start'],
            $previousDateRange['end']
        );

        if ($categoryId) {
            $previousTransactions = $previousTransactions->where('category_id', $categoryId);
        }

        $previousIncome = $previousTransactions->where('type', 'income')->sum('amount');
        $previousExpenses = $previousTransactions->where('type', 'expense')->sum('amount');
        $previousBalance = $previousIncome - $previousExpenses;

        $currentTransactions = $this->transactionRepository->getByDateRange(
            $userId,
            $currentDateRange['start'],
            $currentDateRange['end']
        );

        if ($categoryId) {
            $currentTransactions = $currentTransactions->where('category_id', $categoryId);
        }

        $currentIncome = $currentTransactions->where('type', 'income')->sum('amount');
        $currentExpenses = $currentTransactions->where('type', 'expense')->sum('amount');
        $currentBalance = $currentIncome - $currentExpenses;

        // Calculate percentage changes
        $incomeChange = $previousIncome > 0
            ? (($currentIncome - $previousIncome) / $previousIncome) * 100
            : ($currentIncome > 0 ? 100 : 0);

        $expenseChange = $previousExpenses > 0
            ? (($currentExpenses - $previousExpenses) / $previousExpenses) * 100
            : ($currentExpenses > 0 ? 100 : 0);

        $balanceChange = $previousBalance != 0
            ? (($currentBalance - $previousBalance) / abs($previousBalance)) * 100
            : ($currentBalance > 0 ? 100 : 0);

        return [
            'current' => [
                'income' => $currentIncome,
                'expenses' => $currentExpenses,
                'balance' => $currentBalance,
            ],
            'previous' => [
                'income' => $previousIncome,
                'expenses' => $previousExpenses,
                'balance' => $previousBalance,
            ],
            'changes' => [
                'income' => round($incomeChange, 2),
                'expenses' => round($expenseChange, 2),
                'balance' => round($balanceChange, 2),
            ],
        ];
    }

    /**
     * Helper to get categories with totals.
     */
    protected function getCategoriesWithTotals(Collection $transactions): array
    {
        $categoriesWithTotals = [];
        $totalAmount = $transactions->sum('amount');

        foreach ($transactions as $transaction) {
            $categoryId = $transaction->category_id;
            $category = Category::find($categoryId);
            $categoryName = $category ? $category->name : 'Uncategorized';

            if (! isset($categoriesWithTotals[$categoryId])) {
                $categoriesWithTotals[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'color' => $category ? $category->color : '#607D8B',
                    'icon' => $category ? $category->icon : 'question-circle',
                    'amount' => 0,
                    'percentage' => 0,
                ];
            }

            $categoriesWithTotals[$categoryId]['amount'] += $transaction->amount;
        }

        // Calculate percentages
        foreach ($categoriesWithTotals as &$category) {
            $category['percentage'] = $totalAmount > 0
                ? round(($category['amount'] / $totalAmount) * 100, 2)
                : 0;
        }

        // Sort by amount
        uasort($categoriesWithTotals, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return array_values($categoriesWithTotals);
    }

    /**
     * Helper to analyze spending by weekday.
     */
    protected function getWeekdayAnalysis(Collection $transactions): array
    {
        $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $weekdayTotals = array_fill_keys($weekdays, 0);

        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->date);
            $weekday = $weekdays[$date->dayOfWeek];
            $weekdayTotals[$weekday] += $transaction->amount;
        }

        $chartData = [
            'labels' => array_keys($weekdayTotals),
            'datasets' => [
                [
                    'label' => 'Spending by Day of Week',
                    'data' => array_values($weekdayTotals),
                    'backgroundColor' => [
                        '#f56565', '#ed8936', '#ecc94b', '#48bb78',
                        '#4299e1', '#667eea', '#9f7aea',
                    ],
                ],
            ],
        ];

        return $chartData;
    }

    /**
     * Helper to analyze spending by time of day.
     */
    protected function getTimeOfDayAnalysis(Collection $transactions): array
    {
        $timeCategories = [
            'Morning (6am-12pm)' => 0,
            'Afternoon (12pm-5pm)' => 0,
            'Evening (5pm-9pm)' => 0,
            'Night (9pm-6am)' => 0,
        ];

        foreach ($transactions as $transaction) {
            if (! $transaction->created_at) {
                continue;
            }

            $hour = $transaction->created_at->hour;

            if ($hour >= 6 && $hour < 12) {
                $timeCategories['Morning (6am-12pm)'] += $transaction->amount;
            } elseif ($hour >= 12 && $hour < 17) {
                $timeCategories['Afternoon (12pm-5pm)'] += $transaction->amount;
            } elseif ($hour >= 17 && $hour < 21) {
                $timeCategories['Evening (5pm-9pm)'] += $transaction->amount;
            } else {
                $timeCategories['Night (9pm-6am)'] += $transaction->amount;
            }
        }

        $chartData = [
            'labels' => array_keys($timeCategories),
            'datasets' => [
                [
                    'label' => 'Spending by Time of Day',
                    'data' => array_values($timeCategories),
                    'backgroundColor' => ['#4299e1', '#48bb78', '#ecc94b', '#9f7aea'],
                ],
            ],
        ];

        return $chartData;
    }

    /**
     * Helper to get largest expenses.
     */
    protected function getLargestExpenses(Collection $transactions): array
    {
        return $transactions->sortByDesc('amount')
            ->take(5)
            ->map(function ($transaction) {
                $category = Category::find($transaction->category_id);

                return [
                    'id' => $transaction->id,
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'date' => $transaction->date,
                    'category' => $category ? $category->name : 'Uncategorized',
                ];
            })->values()->toArray();
    }

    /**
     * Helper to get most frequent vendors/payees.
     */
    protected function getFrequentVendors(Collection $transactions): array
    {
        $vendors = [];

        foreach ($transactions as $transaction) {
            $payee = $transaction->payee ?? 'Unknown';

            if (! isset($vendors[$payee])) {
                $vendors[$payee] = [
                    'name' => $payee,
                    'count' => 0,
                    'total' => 0,
                ];
            }

            $vendors[$payee]['count']++;
            $vendors[$payee]['total'] += $transaction->amount;
        }

        // Sort by count
        uasort($vendors, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return array_slice(array_values($vendors), 0, 5);
    }

    /**
     * Helper to get income by category.
     */
    protected function getIncomeByCategory(Collection $transactions): array
    {
        $incomeByCategory = [];
        $totalIncome = $transactions->sum('amount');

        foreach ($transactions as $transaction) {
            $categoryId = $transaction->category_id;
            $category = Category::find($categoryId);
            $categoryName = $category ? $category->name : 'Uncategorized';

            if (! isset($incomeByCategory[$categoryId])) {
                $incomeByCategory[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'color' => $category ? $category->color : '#4299e1',
                    'amount' => 0,
                    'percentage' => 0,
                ];
            }

            $incomeByCategory[$categoryId]['amount'] += $transaction->amount;
        }

        // Calculate percentages
        foreach ($incomeByCategory as &$category) {
            $category['percentage'] = $totalIncome > 0
                ? round(($category['amount'] / $totalIncome) * 100, 2)
                : 0;
        }

        // Sort by amount
        uasort($incomeByCategory, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return array_values($incomeByCategory);
    }

    /**
     * Helper to get income trends over time.
     */
    protected function getIncomeTrends(int $userId, string $period): array
    {
        // For longer periods, we'll show trends by month
        // For shorter periods, we'll show trends by day
        $dateRange = $this->getDateRangeFromPeriod($period);
        $startDate = Carbon::parse($dateRange['start']);
        $endDate = Carbon::parse($dateRange['end']);

        $labels = [];
        $data = [];

        // For month or shorter periods, show daily data
        if (in_array($period, ['week', 'month'])) {
            $daysInRange = $startDate->diffInDays($endDate) + 1;

            for ($i = 0; $i < $daysInRange; $i++) {
                $currentDate = $startDate->copy()->addDays($i);
                $labels[] = $currentDate->format('M d');
                $data[] = 0;
            }

            $transactions = $this->transactionRepository->getByDateRange(
                $userId,
                $dateRange['start'],
                $dateRange['end']
            )->where('type', 'income');

            foreach ($transactions as $transaction) {
                $transactionDate = Carbon::parse($transaction->date);
                $dayIndex = $transactionDate->diffInDays($startDate);

                if ($dayIndex >= 0 && $dayIndex < $daysInRange) {
                    $data[$dayIndex] += $transaction->amount;
                }
            }
        } else {
            // For quarter or year, show monthly data
            $monthsInRange = $startDate->diffInMonths($endDate) + 1;

            for ($i = 0; $i < $monthsInRange; $i++) {
                $currentMonth = $startDate->copy()->addMonths($i);
                $labels[] = $currentMonth->format('M Y');
                $data[] = 0;
            }

            for ($i = 0; $i < $monthsInRange; $i++) {
                $currentMonth = $startDate->copy()->addMonths($i);
                $monthStart = $currentMonth->copy()->startOfMonth()->toDateString();
                $monthEnd = $currentMonth->copy()->endOfMonth()->toDateString();

                $monthlyIncome = $this->transactionRepository->getByDateRange(
                    $userId,
                    $monthStart,
                    $monthEnd
                )->where('type', 'income')->sum('amount');

                $data[$i] = $monthlyIncome;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $data,
                    'borderColor' => '#38a169',
                    'backgroundColor' => 'rgba(56, 161, 105, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Helper to get analytics for a specific period.
     */
    protected function getAnalyticsForPeriod(int $userId, array $dateRange): array
    {
        $transactions = $this->transactionRepository->getByDateRange(
            $userId,
            $dateRange['start'],
            $dateRange['end']
        );

        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');
        $balance = $income - $expenses;

        return [
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $balance,
            'savingsRate' => $income > 0 ? round(($income - $expenses) / $income * 100, 2) : 0,
            'expenseBreakdown' => $this->getExpenseBreakdown($transactions),
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Helper to calculate percent changes between two periods.
     */
    protected function calculatePercentChanges(array $current, array $comparison): array
    {
        $incomeChange = $comparison['income'] > 0
            ? (($current['income'] - $comparison['income']) / $comparison['income']) * 100
            : ($current['income'] > 0 ? 100 : 0);

        $expenseChange = $comparison['expenses'] > 0
            ? (($current['expenses'] - $comparison['expenses']) / $comparison['expenses']) * 100
            : ($current['expenses'] > 0 ? 100 : 0);

        $balanceChange = $comparison['balance'] != 0
            ? (($current['balance'] - $comparison['balance']) / abs($comparison['balance'])) * 100
            : ($current['balance'] > 0 ? 100 : 0);

        $savingsRateChange = $comparison['savingsRate'] != 0
            ? ($current['savingsRate'] - $comparison['savingsRate'])
            : $current['savingsRate'];

        return [
            'income' => round($incomeChange, 2),
            'expenses' => round($expenseChange, 2),
            'balance' => round($balanceChange, 2),
            'savingsRate' => round($savingsRateChange, 2),
        ];
    }
}
