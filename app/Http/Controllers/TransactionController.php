<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
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
     * Display a listing of the transactions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->with('category') // Eager load categories
            ->orderBy('transaction_date', 'desc')
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $categories = $this->transactionService->getCategoriesByType($user->id);

        $incomeCategories = $categories->where('type', 'income')->values();
        $expenseCategories = $categories->where('type', 'expense')->values();
        $transferCategories = $categories->where('type', 'transfer')->values();

        return view('transactions.create', compact('incomeCategories', 'expenseCategories', 'transferCategories'));
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense,transfer',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
        ]);

        $validated['user_id'] = $request->user()->id;

        $this->transactionService->createTransaction($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        $transaction->load('category');

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Request $request, Transaction $transaction): View
    {
        Gate::authorize('update', $transaction);

        $user = $request->user();
        $categories = $this->transactionService->getCategoriesByType($user->id);

        $incomeCategories = $categories->where('type', 'income')->values();
        $expenseCategories = $categories->where('type', 'expense')->values();
        $transferCategories = $categories->where('type', 'transfer')->values();

        return view('transactions.edit', compact('transaction', 'incomeCategories', 'expenseCategories', 'transferCategories'));
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense,transfer',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Search for transactions within a specific bank account.
     * Used for manual matching of staged transactions.
     */
    public function search(Request $request, BankAccount $bankAccount): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('view', $bankAccount); // Ensure user owns the bank account

        $validated = $request->validate([
            'search_term' => 'nullable|string|max:255',
            'search_date_from' => 'nullable|date_format:Y-m-d',
            'search_date_to' => 'nullable|date_format:Y-m-d|after_or_equal:search_date_from',
            'search_amount_min' => 'nullable|numeric',
            'search_amount_max' => 'nullable|numeric|gte:search_amount_min',
            'staged_transaction_id' => 'sometimes|integer|exists:staged_transactions,id', // For context, not direct filtering here
        ]);

        $query = Transaction::where('user_id', $request->user()->id)
            ->where('bank_account_id', $bankAccount->id)
            ->with('category')
            ->orderBy('transaction_date', 'desc');

        if (! empty($validated['search_term'])) {
            $query->where('description', 'LIKE', '%'.$validated['search_term'].'%');
        }

        if (! empty($validated['search_date_from'])) {
            $query->whereDate('transaction_date', '>=', $validated['search_date_from']);
        }

        if (! empty($validated['search_date_to'])) {
            $query->whereDate('transaction_date', '<=', $validated['search_date_to']);
        }

        if (! empty($validated['search_amount_min'])) {
            $query->where('amount', '>=', $validated['search_amount_min']);
        }

        if (! empty($validated['search_amount_max'])) {
            $query->where('amount', '<=', $validated['search_amount_max']);
        }

        // Exclude transactions that are already matched to other staged transactions (not the current one if provided)
        $query->where(function ($q) use ($validated) {
            $q->whereNull('matched_by_staged_transaction_id');
            if (! empty($validated['staged_transaction_id'])) {
                // Allow if it's matched by the *current* staged transaction we are finding a match for
                $q->orWhereHas('matchedStagedTransaction', function ($sq) use ($validated) {
                    $sq->where('id', $validated['staged_transaction_id']);
                });
            }
        });

        $transactions = $query->take(50)->get(); // Limit results for performance

        return response()->json($transactions);
    }
}
