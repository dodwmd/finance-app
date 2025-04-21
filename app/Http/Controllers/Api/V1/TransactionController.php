<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * The transaction repository instance.
     */
    protected $transactionRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        if ($request->has('type')) {
            $transactions = $this->transactionRepository->getByUserIdAndType($userId, $request->type);
        } elseif ($request->has('start_date') && $request->has('end_date')) {
            $transactions = $this->transactionRepository->getByDateRange(
                $userId,
                $request->start_date,
                $request->end_date
            );
        } else {
            $transactions = $this->transactionRepository->getByUserId($userId);
        }

        return response()->json([
            'data' => $transactions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|in:income,expense,transfer',
            'category' => 'required|string|max:50',
            'transaction_date' => 'required|date',
        ]);

        $transaction = $this->transactionRepository->create([
            'user_id' => $request->user()->id,
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'transaction_date' => $validated['transaction_date'],
        ]);

        return response()->json([
            'message' => 'Transaction created successfully',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        // Ensure the transaction belongs to the authenticated user
        $this->authorize('view', $transaction);

        return response()->json([
            'data' => $transaction,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        // Ensure the transaction belongs to the authenticated user
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'type' => 'sometimes|in:income,expense,transfer',
            'category' => 'sometimes|string|max:50',
            'transaction_date' => 'sometimes|date',
        ]);

        $transaction = $this->transactionRepository->update($transaction->id, $validated);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'data' => $transaction,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        // Ensure the transaction belongs to the authenticated user
        $this->authorize('delete', $transaction);

        $this->transactionRepository->delete($transaction->id);

        return response()->json([
            'message' => 'Transaction deleted successfully',
        ]);
    }

    /**
     * Get transaction summary statistics.
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $income = $this->transactionRepository->getSumByType($userId, 'income');
        $expenses = $this->transactionRepository->getSumByType($userId, 'expense');

        return response()->json([
            'data' => [
                'income' => $income,
                'expenses' => $expenses,
                'balance' => $income - $expenses,
            ],
        ]);
    }
}
