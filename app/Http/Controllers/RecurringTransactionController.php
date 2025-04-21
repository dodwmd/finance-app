<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    /**
     * The recurring transaction service instance.
     */
    protected $recurringTransactionService;

    /**
     * Create a new controller instance.
     */
    public function __construct(RecurringTransactionService $recurringTransactionService)
    {
        $this->recurringTransactionService = $recurringTransactionService;
    }

    /**
     * Display a listing of the recurring transactions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $recurringTransactions = RecurringTransaction::where('user_id', $user->id)
            ->with('category')
            ->orderBy('next_due_date')
            ->paginate(10);

        return view('recurring-transactions.index', compact('recurringTransactions'));
    }

    /**
     * Show the form for creating a new recurring transaction.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $categories = $user->categories()->orderBy('name')->get();

        $incomeCategories = $categories->where('type', 'income')->values();
        $expenseCategories = $categories->where('type', 'expense')->values();
        $transferCategories = $categories->where('type', 'transfer')->values();

        return view('recurring-transactions.create', compact('incomeCategories', 'expenseCategories', 'transferCategories'));
    }

    /**
     * Store a newly created recurring transaction in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense,transfer',
            'category_id' => 'required|exists:categories,id',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,quarterly,annually',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Set default values
        $validated['user_id'] = $request->user()->id;
        $validated['next_due_date'] = $validated['start_date'];
        $validated['status'] = 'active';

        // Create the recurring transaction directly for now to avoid dependency issues
        RecurringTransaction::create($validated);

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Recurring transaction created successfully.');
    }

    /**
     * Display the specified recurring transaction.
     */
    public function show(RecurringTransaction $recurringTransaction): View
    {
        Gate::authorize('view', $recurringTransaction);

        $recurringTransaction->load('category');

        return view('recurring-transactions.show', compact('recurringTransaction'));
    }

    /**
     * Show the form for editing the specified recurring transaction.
     */
    public function edit(Request $request, RecurringTransaction $recurringTransaction): View
    {
        Gate::authorize('update', $recurringTransaction);

        $user = $request->user();
        $categories = $user->categories()->orderBy('name')->get();

        $incomeCategories = $categories->where('type', 'income')->values();
        $expenseCategories = $categories->where('type', 'expense')->values();
        $transferCategories = $categories->where('type', 'transfer')->values();

        return view('recurring-transactions.edit', compact('recurringTransaction', 'incomeCategories', 'expenseCategories', 'transferCategories'));
    }

    /**
     * Update the specified recurring transaction in storage.
     */
    public function update(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        Gate::authorize('update', $recurringTransaction);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense,transfer',
            'category_id' => 'required|exists:categories,id',
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,quarterly,annually',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,paused',
        ]);

        $recurringTransaction->update($validated);

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Recurring transaction updated successfully.');
    }

    /**
     * Remove the specified recurring transaction from storage.
     */
    public function destroy(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        Gate::authorize('delete', $recurringTransaction);

        $recurringTransaction->delete();

        return redirect()->route('recurring-transactions.index')
            ->with('success', 'Recurring transaction deleted successfully.');
    }

    /**
     * Toggle the status of a recurring transaction.
     */
    public function toggleStatus(RecurringTransaction $recurringTransaction): RedirectResponse
    {
        Gate::authorize('update', $recurringTransaction);

        $newStatus = $recurringTransaction->status === 'active' ? 'paused' : 'active';
        $recurringTransaction->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'activated' : 'paused';

        return redirect()->route('recurring-transactions.index')
            ->with('success', "Recurring transaction {$statusText} successfully.");
    }
}
