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

        // Pre-process ALL category data for JavaScript to avoid Blade template syntax issues
        $currentCategories = [];
        foreach ($categoryComparison['current'] as $category) {
            $currentCategories[] = [
                'name' => $category['name'],
                'amount' => $category['amount'],
                'color' => $category['color'],
            ];
        }

        $previousCategories = [];
        foreach ($categoryComparison['previous'] as $category) {
            $previousCategories[] = [
                'name' => $category['name'],
                'amount' => $category['amount'],
                'color' => $category['color'],
            ];
        }

        // Get all unique category names for the chart
        $allCategoryNames = [];
        $temp = array_merge($currentCategories, $previousCategories);
        foreach ($temp as $category) {
            if (! in_array($category['name'], $allCategoryNames)) {
                $allCategoryNames[] = $category['name'];
            }
        }

        // Prepare data for the chart
        $currentAmounts = [];
        $previousAmounts = [];
        $backgroundColors = [];
        $colorMap = [];

        // Create color mapping
        foreach ($currentCategories as $category) {
            $colorMap[$category['name']] = $category['color'];
        }

        foreach ($allCategoryNames as $categoryName) {
            $currentAmount = 0;
            foreach ($currentCategories as $category) {
                if ($category['name'] === $categoryName) {
                    $currentAmount = $category['amount'];
                    break;
                }
            }
            $currentAmounts[] = $currentAmount;

            $previousAmount = 0;
            foreach ($previousCategories as $category) {
                if ($category['name'] === $categoryName) {
                    $previousAmount = $category['amount'];
                    break;
                }
            }
            $previousAmounts[] = $previousAmount;

            $color = isset($colorMap[$categoryName]) ? $colorMap[$categoryName] : '#607D8B';
            $backgroundColors[] = $color;
        }

        // Get spending patterns data
        $spendingPatterns = $this->analyticsService->getSpendingPatterns($user->id, $period);

        return view('analytics.expenses', compact(
            'expenseTrends',
            'categoryComparison',
            'currentCategories',
            'previousCategories',
            'allCategoryNames',
            'currentAmounts',
            'previousAmounts',
            'backgroundColors',
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
