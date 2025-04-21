<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * The category service instance.
     */
    protected $categoryService;

    /**
     * Create a new controller instance.
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $categories = $this->categoryService->getCategoriesByUser($user->id);

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,transfer',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
        ]);

        $validated['user_id'] = $request->user()->id;

        $this->categoryService->createCategory($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * API endpoint to store a category and return JSON.
     */
    public function storeApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,transfer',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $request->user()->id;

        // Set defaults if not provided
        if (empty($data['color'])) {
            $data['color'] = $this->categoryService->generateRandomColor();
        }

        if (empty($data['icon'])) {
            // Default icon based on type
            $icons = [
                'income' => 'money-bill',
                'expense' => 'shopping-cart',
                'transfer' => 'exchange-alt',
            ];
            $data['icon'] = $icons[$data['type']];
        }

        $category = $this->categoryService->createCategory($data);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    /**
     * Display the specified category.
     */
    public function show(int $id): View
    {
        $category = Category::findOrFail($id);

        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(int $id): View
    {
        $category = Category::findOrFail($id);

        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,transfer',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
        ]);

        $this->categoryService->updateCategory($id, $validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->categoryService->deleteCategory($id);

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    /**
     * Get categories by type for a specific user.
     */
    public function getByType(Request $request, ?string $type = null): JsonResponse
    {
        $userId = $request->user()->id;
        $categories = $this->categoryService->getCategoriesByType($userId, $type);

        return response()->json($categories);
    }
}
