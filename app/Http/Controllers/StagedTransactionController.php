<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\StagedTransaction;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StagedTransactionController extends Controller
{
    /**
     * Approve a staged transaction and convert it into a real transaction.
     */
    public function approve(Request $_request, StagedTransaction $stagedTransaction): RedirectResponse
    {
        // Authorization: Ensure the user owns the bank account associated with the staged transaction
        if ($stagedTransaction->user_id !== Auth::id() || $stagedTransaction->bankAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($stagedTransaction->status !== 'pending_review') {
            return redirect()->route('bank-accounts.staged.review', $stagedTransaction->bank_account_id)
                ->with('error', 'This transaction is not pending review and cannot be approved.');
        }

        DB::beginTransaction();
        try {
            // 1. Create the actual Transaction
            $transaction = Transaction::create([
                'user_id' => $stagedTransaction->user_id,
                'bank_account_id' => $stagedTransaction->bank_account_id,
                'description' => $stagedTransaction->description,
                'amount' => $stagedTransaction->amount,
                // If category_id is set on stagedTransaction (e.g., by user during review), use it
                'category_id' => $stagedTransaction->suggested_category_id,
                'transaction_date' => $stagedTransaction->transaction_date,
                'type' => $stagedTransaction->amount >= 0 ? 'income' : 'expense', // Determine type based on amount
            ]);

            // 2. Update BankAccount balance
            $bankAccount = $stagedTransaction->bankAccount;
            $bankAccount->current_balance += $stagedTransaction->amount;
            $bankAccount->save();

            // 3. Update StagedTransaction status
            $stagedTransaction->status = 'imported';
            $stagedTransaction->matched_transaction_id = $transaction->id;
            $stagedTransaction->save();

            DB::commit();

            return redirect()->route('bank-accounts.staged.review', $stagedTransaction->bank_account_id)
                ->with('success', 'Transaction approved and imported successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error approving staged transaction {$stagedTransaction->id}: ".$e->getMessage());

            return redirect()->route('bank-accounts.staged.review', $stagedTransaction->bank_account_id)
                ->with('error', 'Error approving transaction: '.$e->getMessage());
        }
    }

    /**
     * Update the suggested category for a staged transaction.
     */
    public function updateCategory(Request $request, StagedTransaction $stagedTransaction): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        // Authorization: Ensure the user owns the bank account associated with the staged transaction
        // and the category.
        if ($stagedTransaction->user_id !== Auth::id() ||
            $stagedTransaction->bankAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action on staged transaction.');
        }

        $category = \App\Models\Category::find($validated['category_id']);
        if (! $category || $category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action on category.');
        }

        if ($stagedTransaction->status !== 'pending_review') {
            return redirect()->route('bank-accounts.staged.review', $stagedTransaction->bank_account_id)
                ->with('error', 'This transaction is not pending review and cannot be updated.');
        }

        try {
            $stagedTransaction->suggested_category_id = $validated['category_id'];
            $stagedTransaction->save();

            return redirect()->route('bank-accounts.staged.review', ['bankAccount' => $stagedTransaction->bank_account_id, 'page' => $request->query('page', '1')])
                ->with('success', 'Transaction category updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating category for staged transaction {$stagedTransaction->id}: ".$e->getMessage());

            return redirect()->route('bank-accounts.staged.review', $stagedTransaction->bank_account_id)
                ->with('error', 'Error updating transaction category: '.$e->getMessage());
        }
    }

    /**
     * Mark a staged transaction as 'ignored'.
     */
    public function ignore(Request $request, StagedTransaction $stagedTransaction): RedirectResponse
    {
        // Authorization: Ensure the user owns the bank account associated with the staged transaction
        if ($stagedTransaction->user_id !== Auth::id() ||
            $stagedTransaction->bankAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (! in_array($stagedTransaction->status, ['pending_review', 'potential_duplicate'])) {
            return redirect()->route('bank-accounts.staged.review', $stagedTransaction->bank_account_id)
                ->with('error', 'This transaction is not in a state where it can be ignored.');
        }

        try {
            $stagedTransaction->status = 'ignored';
            $stagedTransaction->save();

            return redirect()->route('bank-accounts.staged.review', ['bankAccount' => $stagedTransaction->bank_account_id, 'page' => $request->query('page', '1')])
                ->with('success', 'Transaction marked as ignored.');
        } catch (\Exception $e) {
            Log::error("Error ignoring staged transaction {$stagedTransaction->id}: ".$e->getMessage());

            return redirect()->route('bank-accounts.staged.review', $stagedTransaction->bank_account_id)
                ->with('error', 'Error ignoring transaction: '.$e->getMessage());
        }
    }

    /**
     * Mark a potential duplicate staged transaction as not a duplicate.
     */
    public function unmatch(Request $request, StagedTransaction $stagedTransaction): RedirectResponse
    {
        // Authorization: Ensure the user owns the bank account associated with the staged transaction
        if ($stagedTransaction->user_id !== Auth::id() ||
            $stagedTransaction->bankAccount->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($stagedTransaction->status !== 'potential_duplicate') {
            return redirect()->route('bank-accounts.staged.review', ['bankAccount' => $stagedTransaction->bank_account_id, 'page' => $request->query('page', '1')])
                ->with('error', 'This transaction is not marked as a potential duplicate.');
        }

        try {
            $stagedTransaction->status = 'pending_review';
            $stagedTransaction->matched_transaction_id = null;
            $stagedTransaction->save();

            return redirect()->route('bank-accounts.staged.review', ['bankAccount' => $stagedTransaction->bank_account_id, 'page' => $request->query('page', '1')])
                ->with('success', 'Transaction marked as not a duplicate and is now pending review.');
        } catch (\Exception $e) {
            Log::error("Error unmatching staged transaction {$stagedTransaction->id}: ".$e->getMessage());

            return redirect()->route('bank-accounts.staged.review', ['bankAccount' => $stagedTransaction->bank_account_id, 'page' => $request->query('page', '1')])
                ->with('error', 'Error unmatching transaction: '.$e->getMessage());
        }
    }

    /**
     * Manually match a staged transaction to an existing transaction.
     */
    public function manualMatch(StagedTransaction $stagedTransaction, Transaction $transaction): \Illuminate\Http\JsonResponse
    {
        // Authorization: Ensure the user owns both the staged transaction and the target transaction
        if ($stagedTransaction->user_id !== Auth::id() ||
            $transaction->user_id !== Auth::id() ||
            $stagedTransaction->bank_account_id !== $transaction->bank_account_id) {
            return response()->json(['error' => 'Unauthorized action or mismatched bank accounts.'], 403);
        }

        // Ensure the target transaction is not already matched by another staged transaction (unless it's this one)
        if ($transaction->matched_by_staged_transaction_id !== null && $transaction->matched_by_staged_transaction_id !== $stagedTransaction->id) {
            return response()->json(['error' => 'The selected existing transaction is already matched by another staged transaction.'], 422);
        }

        // Ensure staged transaction can be matched
        if (! in_array($stagedTransaction->status, ['pending_review', 'potential_duplicate'])) {
            return response()->json(['error' => 'This staged transaction cannot be manually matched in its current state.'], 422);
        }

        DB::beginTransaction();
        try {
            // Clear any previous match on the StagedTransaction
            if ($stagedTransaction->matched_transaction_id) {
                $oldMatchedTransaction = Transaction::find($stagedTransaction->matched_transaction_id);
                if ($oldMatchedTransaction && $oldMatchedTransaction->matched_by_staged_transaction_id === $stagedTransaction->id) {
                    $oldMatchedTransaction->matched_by_staged_transaction_id = null;
                    $oldMatchedTransaction->save();
                }
            }

            // Clear any previous match on the new target Transaction if it was somehow matched to a *different* staged one
            // (This scenario should be rare due to the check above, but as a safeguard)
            if ($transaction->matched_by_staged_transaction_id !== null && $transaction->matched_by_staged_transaction_id !== $stagedTransaction->id) {
                $otherStagedTxn = StagedTransaction::find($transaction->matched_by_staged_transaction_id);
                if ($otherStagedTxn) {
                    $otherStagedTxn->matched_transaction_id = null;
                    $otherStagedTxn->status = 'pending_review'; // Revert its status
                    $otherStagedTxn->save();
                }
            }

            // Apply the new match
            $stagedTransaction->status = 'potential_duplicate'; // Or 'manually_matched'
            $stagedTransaction->matched_transaction_id = $transaction->id;
            $stagedTransaction->save();

            $transaction->matched_by_staged_transaction_id = $stagedTransaction->id;
            $transaction->save();

            DB::commit();

            return response()->json(['success' => 'Transaction manually matched successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error manually matching staged transaction {$stagedTransaction->id} to transaction {$transaction->id}: ".$e->getMessage());

            return response()->json(['error' => 'Error manually matching transaction: '.$e->getMessage()], 500);
        }
    }
}
