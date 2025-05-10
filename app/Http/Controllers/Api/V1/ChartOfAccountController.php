<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = ChartOfAccount::where('user_id', Auth::id())
            ->orderBy('account_code')
            ->paginate(config('app.pagination_size', 15)); // Use a configurable page size

        return ChartOfAccountResource::collection($accounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChartOfAccountRequest $request)
    {
        $validatedData = $request->validated();

        $account = new ChartOfAccount($validatedData);
        $account->user_id = Auth::id(); // Ensure user_id is set to the authenticated user
        $account->save();

        return (new ChartOfAccountResource($account))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Optionally load parent and children if you always want them or based on request parameters
        // $chartOfAccount->load(['parent', 'children']);
        return new ChartOfAccountResource($chartOfAccount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validatedData = $request->validated();
        $chartOfAccount->update($validatedData);

        return new ChartOfAccountResource($chartOfAccount);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if ($chartOfAccount->children()->count() > 0) {
            return response()->json(['error' => 'Cannot delete account with child accounts.'], 422); // Or 409 Conflict
        }

        $chartOfAccount->delete();

        return response()->json(null, 204);
    }
}
