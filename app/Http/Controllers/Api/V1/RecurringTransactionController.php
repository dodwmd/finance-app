<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecurringTransactionResource;
use App\Models\RecurringTransaction;
use App\Repositories\RecurringTransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecurringTransactionController extends Controller
{
    /**
     * The recurring transaction repository instance.
     */
    protected $recurringTransactionRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(RecurringTransactionRepository $recurringTransactionRepository)
    {
        $this->recurringTransactionRepository = $recurringTransactionRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        if ($request->has('type')) {
            $transactions = $this->recurringTransactionRepository->getByUserIdAndType($userId, $request->type);
        } elseif ($request->has('frequency')) {
            $transactions = $this->recurringTransactionRepository->getByUserIdAndFrequency($userId, $request->frequency);
        } else {
            $transactions = $this->recurringTransactionRepository->getByUserId($userId);
        }

        return RecurringTransactionResource::collection($transactions);
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
            'frequency' => 'required|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $transaction = $this->recurringTransactionRepository->create([
            'user_id' => $request->user()->id,
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'last_processed_date' => null,
        ]);

        return response()->json([
            'message' => 'Recurring transaction created successfully',
            'data' => new RecurringTransactionResource($transaction),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RecurringTransaction $recurringTransaction): JsonResponse
    {
        // Ensure the transaction belongs to the authenticated user
        $this->authorize('view', $recurringTransaction);

        return response()->json([
            'data' => new RecurringTransactionResource($recurringTransaction),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecurringTransaction $recurringTransaction): JsonResponse
    {
        // Ensure the transaction belongs to the authenticated user
        $this->authorize('update', $recurringTransaction);

        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'type' => 'sometimes|in:income,expense,transfer',
            'category' => 'sometimes|string|max:50',
            'frequency' => 'sometimes|in:daily,weekly,biweekly,monthly,quarterly,yearly',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $transaction = $this->recurringTransactionRepository->update($recurringTransaction->id, $validated);

        return response()->json([
            'message' => 'Recurring transaction updated successfully',
            'data' => new RecurringTransactionResource($transaction),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringTransaction $recurringTransaction): JsonResponse
    {
        // Ensure the transaction belongs to the authenticated user
        $this->authorize('delete', $recurringTransaction);

        $this->recurringTransactionRepository->delete($recurringTransaction->id);

        return response()->json([
            'message' => 'Recurring transaction deleted successfully',
        ]);
    }

    /**
     * Get due recurring transactions.
     */
    public function due(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $dueTransactions = $this->recurringTransactionRepository->getDueRecurringTransactions($userId);

        return response()->json([
            'data' => RecurringTransactionResource::collection($dueTransactions),
        ]);
    }
}
