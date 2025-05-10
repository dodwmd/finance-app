<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Models\ChartOfAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = ChartOfAccount::where('user_id', Auth::id())
            ->orderBy('account_code')
            ->paginate(15); // Or a configurable number

        return view('chart-of-accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch accounts for the current user to populate parent_id dropdown
        $parentAccounts = ChartOfAccount::where('user_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'account_code']);

        // Define allowed account types (could also come from a config or Enum in a real app)
        $accountTypes = ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense', 'CostOfGoodsSold'];

        return view('chart-of-accounts.create', compact('parentAccounts', 'accountTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChartOfAccountRequest $request)
    {
        $validatedData = $request->validated();

        $account = new ChartOfAccount;
        $account->user_id = Auth::id();
        $account->account_code = $validatedData['account_code'];
        $account->name = $validatedData['name'];
        $account->type = strtolower($validatedData['type']); // Ensure lowercase
        $account->description = $validatedData['description'] ?? null;
        $account->parent_id = $validatedData['parent_id'] ?? null;
        $account->is_active = $validatedData['is_active'] ?? true; // Default to true if not present
        $account->allow_direct_posting = $validatedData['allow_direct_posting'] ?? true; // Default to true
        $account->system_account_tag = $validatedData['system_account_tag'] ?? null;
        $account->save();

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        // Typically, you might want to authorize here as well if not done by middleware
        if ($chartOfAccount->user_id !== Auth::id()) {
            abort(403);
        }

        return view('chart-of-accounts.show', compact('chartOfAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        // Authorization: Ensure the user owns this account
        if ($chartOfAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Fetch accounts for the current user to populate parent_id dropdown,
        // excluding the current account itself.
        $parentAccounts = ChartOfAccount::where('user_id', Auth::id())
            ->where('id', '!=', $chartOfAccount->id) // Exclude self
            ->orderBy('name')
            ->get(['id', 'name', 'account_code']);

        $accountTypes = ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense', 'CostOfGoodsSold'];

        return view('chart-of-accounts.edit', compact('chartOfAccount', 'parentAccounts', 'accountTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chartOfAccount): RedirectResponse
    {
        // Authorization is handled by UpdateChartOfAccountRequest's authorize method

        $validatedData = $request->validated();

        // Log the value and type of is_active before assignment
        Log::debug('ChartOfAccount Update - Validated is_active:', [
            'value' => $validatedData['is_active'] ?? null, // Handle if not present
            'type' => gettype($validatedData['is_active'] ?? null),
        ]);

        if (isset($validatedData['account_code'])) {
            $chartOfAccount->account_code = $validatedData['account_code'];
        }
        if (isset($validatedData['name'])) {
            $chartOfAccount->name = $validatedData['name'];
        }
        if (isset($validatedData['type'])) {
            $chartOfAccount->type = strtolower($validatedData['type']); // Ensure lowercase
        }

        $chartOfAccount->description = $validatedData['description'] ?? null;
        $chartOfAccount->parent_id = $validatedData['parent_id'] ?? null;
        $chartOfAccount->system_account_tag = $validatedData['system_account_tag'] ?? null;

        // is_active and allow_direct_posting are guaranteed to be boolean by prepareForValidation
        // and are required, so they should always be present.
        $chartOfAccount->is_active = $validatedData['is_active'];
        $chartOfAccount->allow_direct_posting = $validatedData['allow_direct_posting'];

        $chartOfAccount->save();

        // Log the model's is_active state after save
        Log::debug('ChartOfAccount Update - Model is_active after save:', [
            'value' => $chartOfAccount->is_active,
            'type' => gettype($chartOfAccount->is_active),
            'is_dirty' => $chartOfAccount->isDirty('is_active'),
            'was_changed' => $chartOfAccount->wasChanged('is_active'),
        ]);

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        // Authorization: Ensure the user owns this account
        if ($chartOfAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check for child accounts
        if ($chartOfAccount->children()->count() > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account: It has child accounts. Please reassign or delete them first.');
        }

        // Optionally, check if the account is linked to transactions before deleting
        // This would require a relationship and check, e.g., if ($chartOfAccount->transactions()->count() > 0)
        // For now, we'll proceed with a simple delete if no children.

        $chartOfAccount->delete();

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
