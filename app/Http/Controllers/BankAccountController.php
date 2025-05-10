<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Models\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Placeholder for listing bank accounts - to be implemented later
        $bankAccounts = BankAccount::where('user_id', Auth::id())->latest()->paginate(15);

        return view('bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('bank-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = Auth::id();
        // Set current_balance to opening_balance on creation
        $validatedData['current_balance'] = $validatedData['opening_balance'];

        BankAccount::create($validatedData);

        return redirect()->route('bank-accounts.index') // Or dashboard, or a success page
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount): View
    {
        // Ensure the user owns this account
        if ($bankAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // The view 'bank-accounts.show' will be created in a subsequent step.
        return view('bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): View
    {
        if ($bankAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('bank-accounts.edit', compact('bankAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        // Authorization is handled by UpdateBankAccountRequest
        $validatedData = $request->validated();
        $bankAccount->update($validatedData);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        // Ensure the user owns this account
        if ($bankAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Basic delete. Add checks for transactions later if needed.
        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
    }
}
