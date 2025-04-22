<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $categories = Category::where('user_id', $userId)
            ->when($request->has('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): CategoryResource
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,transfer',
        ]);

        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
        ]);

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): CategoryResource
    {
        // Ensure the category belongs to the authenticated user
        $this->authorize('view', $category);

        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): CategoryResource
    {
        // Ensure the category belongs to the authenticated user
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:income,expense,transfer',
        ]);

        $category->update($validated);

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Ensure the category belongs to the authenticated user
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
