<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * The analytics service instance.
     */
    protected $analyticsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->middleware('auth');
    }

    /**
     * Show the main analytics dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $period = $request->query('period', 'month');
        $categoryId = $request->query('category', null);

        // Get analytics data based on period
        $analyticsData = $this->analyticsService->getAnalyticsData($user->id, $period, $categoryId);

        // Get available categories for filter
        $categories = $this->analyticsService->getUserCategories($user->id);

        return view('analytics.index', compact(
            'analyticsData',
            'categories',
            'period',
            'categoryId'
        ));
    }

    /**
     * Show detailed expense breakdown.
     */
    public function expenses(Request $request): View
    {
        $user = $request->user();
        $period = $request->query('period', 'month');

        // Get expense trends data
        $expenseTrends = $this->analyticsService->getExpenseTrends($user->id, $period);

        // Get category comparison data
        $categoryComparison = $this->analyticsService->getCategoryComparison($user->id, $period);

        // Get spending patterns data
        $spendingPatterns = $this->analyticsService->getSpendingPatterns($user->id, $period);

        return view('analytics.expenses', compact(
            'expenseTrends',
            'categoryComparison',
            'spendingPatterns',
            'period'
        ));
    }

    /**
     * Show income analysis.
     */
    public function income(Request $request): View
    {
        $user = $request->user();
        $period = $request->query('period', 'month');

        // Get income analysis data
        $incomeAnalysis = $this->analyticsService->getIncomeAnalysis($user->id, $period);

        return view('analytics.income', compact(
            'incomeAnalysis',
            'period'
        ));
    }

    /**
     * Show comparison over time.
     */
    public function comparison(Request $request): View
    {
        $user = $request->user();
        $period = $request->query('period', 'year');
        $compareWith = $request->query('compare', 'previous');

        // Get comparison data
        $comparisonData = $this->analyticsService->getComparisonData($user->id, $period, $compareWith);

        return view('analytics.comparison', compact(
            'comparisonData',
            'period',
            'compareWith'
        ));
    }
}
