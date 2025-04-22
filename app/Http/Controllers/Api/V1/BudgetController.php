<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BudgetResource;
use App\Models\Budget;
use App\Repositories\BudgetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BudgetController extends Controller
{
    /**
     * The budget repository instance.
     */
    protected $budgetRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(BudgetRepository $budgetRepository)
    {
        $this->budgetRepository = $budgetRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        // Get budgets with optional period filtering
        if ($request->has('period')) {
            $budgets = $this->budgetRepository->getByUserIdAndPeriod($userId, $request->period);
        } else {
            $budgets = $this->budgetRepository->getByUserId($userId);
        }

        return BudgetResource::collection($budgets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $budget = $this->budgetRepository->create([
            'user_id' => $request->user()->id,
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'period' => $validated['period'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return response()->json([
            'message' => 'Budget created successfully',
            'data' => new BudgetResource($budget),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget): JsonResponse
    {
        // Ensure the budget belongs to the authenticated user
        $this->authorize('view', $budget);

        return response()->json([
            'data' => new BudgetResource($budget),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget): JsonResponse
    {
        // Ensure the budget belongs to the authenticated user
        $this->authorize('update', $budget);

        $validated = $request->validate([
            'category' => 'sometimes|string|max:50',
            'amount' => 'sometimes|numeric|min:0',
            'period' => 'sometimes|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $budget = $this->budgetRepository->update($budget->id, $validated);

        return response()->json([
            'message' => 'Budget updated successfully',
            'data' => new BudgetResource($budget),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget): JsonResponse
    {
        // Ensure the budget belongs to the authenticated user
        $this->authorize('delete', $budget);

        $this->budgetRepository->delete($budget->id);

        return response()->json([
            'message' => 'Budget deleted successfully',
        ]);
    }

    /**
     * Get budget progress statistics.
     */
    public function progress(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'period' => 'required|in:weekly,monthly,quarterly,yearly',
        ]);

        $progress = $this->budgetRepository->getBudgetProgress($userId, $validated['period']);

        return response()->json([
            'data' => $progress,
        ]);
    }
}
