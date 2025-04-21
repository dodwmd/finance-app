<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The transaction service instance.
     */
    protected $transactionService;

    /**
     * Create a new controller instance.
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Show the application dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get current month's data
        $monthlySummary = $this->transactionService->getMonthlySummary(
            $user->id,
            $currentMonth,
            $currentYear
        );

        // Get current balance
        $balance = $this->transactionService->getUserBalance($user->id);

        // Get expense categories with totals
        $expenseCategories = $this->transactionService->getExpenseCategoriesWithTotals($user->id);

        // Get recent transactions (last 5)
        $recentTransactions = $monthlySummary['transactions']->take(5);

        // Generate monthly data for the last 6 months for chart
        $chartData = $this->getMonthlyChartData($user->id);

        return view('dashboard', compact(
            'balance',
            'monthlySummary',
            'expenseCategories',
            'recentTransactions',
            'chartData'
        ));
    }

    /**
     * Get monthly chart data for the last 6 months.
     */
    private function getMonthlyChartData(int $userId): array
    {
        $chartData = [
            'labels' => [],
            'income' => [],
            'expenses' => [],
        ];

        // Get data for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $summary = $this->transactionService->getMonthlySummary(
                $userId,
                $date->month,
                $date->year
            );

            $chartData['labels'][] = $date->format('M Y');
            $chartData['income'][] = $summary['income'];
            $chartData['expenses'][] = $summary['expenses'];
        }

        return $chartData;
    }
}
