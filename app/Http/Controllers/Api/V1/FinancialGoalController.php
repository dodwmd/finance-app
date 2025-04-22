<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FinancialGoalResource;
use App\Models\FinancialGoal;
use App\Repositories\FinancialGoalRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinancialGoalController extends Controller
{
    /**
     * The financial goal repository instance.
     */
    protected $financialGoalRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(FinancialGoalRepository $financialGoalRepository)
    {
        $this->financialGoalRepository = $financialGoalRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        if ($request->has('is_completed')) {
            $goals = $this->financialGoalRepository->getByUserIdAndStatus(
                $userId,
                filter_var($request->is_completed, FILTER_VALIDATE_BOOLEAN)
            );
        } else {
            $goals = $this->financialGoalRepository->getByUserId($userId);
        }

        return FinancialGoalResource::collection($goals);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
        ]);

        $goal = $this->financialGoalRepository->create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'target_amount' => $validated['target_amount'],
            'current_amount' => $validated['current_amount'] ?? 0,
            'due_date' => $validated['due_date'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'is_completed' => false,
        ]);

        return response()->json([
            'message' => 'Financial goal created successfully',
            'data' => new FinancialGoalResource($goal),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FinancialGoal $financialGoal): JsonResponse
    {
        // Ensure the goal belongs to the authenticated user
        $this->authorize('view', $financialGoal);

        return response()->json([
            'data' => new FinancialGoalResource($financialGoal),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinancialGoal $financialGoal): JsonResponse
    {
        // Ensure the goal belongs to the authenticated user
        $this->authorize('update', $financialGoal);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'target_amount' => 'sometimes|numeric|min:0',
            'current_amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:50',
            'is_completed' => 'sometimes|boolean',
        ]);

        $goal = $this->financialGoalRepository->update($financialGoal->id, $validated);

        return response()->json([
            'message' => 'Financial goal updated successfully',
            'data' => new FinancialGoalResource($goal),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialGoal $financialGoal): JsonResponse
    {
        // Ensure the goal belongs to the authenticated user
        $this->authorize('delete', $financialGoal);

        $this->financialGoalRepository->delete($financialGoal->id);

        return response()->json([
            'message' => 'Financial goal deleted successfully',
        ]);
    }

    /**
     * Get goal progress statistics.
     */
    public function progress(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $progress = $this->financialGoalRepository->getGoalProgress($userId);

        return response()->json([
            'data' => $progress,
        ]);
    }
}
