<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\BudgetService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * The budget service instance.
     *
     * @var BudgetService
     */
    protected $budgetService;

    /**
     * The transaction service instance.
     *
     * @var TransactionService
     */
    protected $transactionService;

    /**
     * Create a new controller instance.
     */
    public function __construct(BudgetService $budgetService, TransactionService $transactionService)
    {
        $this->budgetService = $budgetService;
        $this->transactionService = $transactionService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the budgets.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $period = $request->query('period');

        $budgets = $this->budgetService->getAllBudgets($user->id);
        $activeBudgets = $this->budgetService->getActiveBudgetsWithProgress($user->id, $period);
        $overview = $this->budgetService->getBudgetOverview($user->id);
        $periodOptions = $this->budgetService->getPeriodOptions();

        return view('budgets.index', compact(
            'budgets',
            'activeBudgets',
            'overview',
            'periodOptions',
            'period'
        ));
    }

    /**
     * Show the form for creating a new budget.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $categories = $this->transactionService->getCategoriesByType($user->id);
        $periodOptions = $this->budgetService->getPeriodOptions();

        return view('budgets.create', compact('categories', 'periodOptions'));
    }

    /**
     * Store a newly created budget in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['is_active'] = $request->has('is_active');

        $budget = $this->budgetService->createBudget($validated);

        return redirect()->route('budgets.show', $budget)
            ->with('success', 'Budget created successfully!');
    }

    /**
     * Display the specified budget.
     */
    public function show(int $id): View
    {
        $budget = $this->budgetService->getBudgetById($id);

        if (! $budget || Gate::denies('view', $budget)) {
            abort(403, 'Unauthorized action.');
        }

        $budgetProgress = $this->budgetService->getBudgetProgress($id);

        return view('budgets.show', compact('budget', 'budgetProgress'));
    }

    /**
     * Show the form for editing the specified budget.
     */
    public function edit(int $id): View
    {
        $budget = $this->budgetService->getBudgetById($id);

        if (! $budget || Gate::denies('update', $budget)) {
            abort(403, 'Unauthorized action.');
        }

        $categories = $this->transactionService->getCategoriesByType($budget->user_id);
        $periodOptions = $this->budgetService->getPeriodOptions();

        return view('budgets.edit', compact('budget', 'categories', 'periodOptions'));
    }

    /**
     * Update the specified budget in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $budget = $this->budgetService->getBudgetById($id);

        if (! $budget || Gate::denies('update', $budget)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->budgetService->updateBudget($id, $validated);

        return redirect()->route('budgets.show', $id)
            ->with('success', 'Budget updated successfully!');
    }

    /**
     * Remove the specified budget from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $budget = $this->budgetService->getBudgetById($id);

        if (! $budget || Gate::denies('delete', $budget)) {
            abort(403, 'Unauthorized action.');
        }

        $this->budgetService->deleteBudget($id);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully!');
    }

    /**
     * Display the budget progress and analytics.
     */
    public function showProgress(int $id): View
    {
        $budget = $this->budgetService->getBudgetById($id);

        if (! $budget || Gate::denies('view', $budget)) {
            abort(403, 'Unauthorized action.');
        }

        $budgetProgress = $this->budgetService->getBudgetProgress($id);

        // Get transactions for this budget's category within its time period
        $transactions = $this->transactionService->getTransactionsByCategoryAndDateRange(
            $budget->user_id,
            $budget->category_id,
            $budgetProgress['start_date'],
            $budgetProgress['end_date']
        );

        return view('budgets.progress', compact('budget', 'budgetProgress', 'transactions'));
    }
}
