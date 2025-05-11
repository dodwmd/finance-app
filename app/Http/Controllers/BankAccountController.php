<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Http\Requests\UpdateBankStatementMappingRequest;
use App\Models\BankAccount;
use App\Models\BankStatementImport;
use App\Models\StagedTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use League\Csv\Reader;
use League\Csv\Statement;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', BankAccount::class);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $bankAccounts = $user->bankAccounts()->with('latestTransaction')->orderBy('account_name')->paginate(15);

        return view('bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Display the CSV column mapping form for a specific import.
     */
    public function showMappingForm(BankAccount $bankAccount, BankStatementImport $import): View
    {
        $this->authorize('view', $bankAccount);
        if ($import->bank_account_id !== $bankAccount->id || $import->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('bank-accounts.import.mapping', [
            'bankAccount' => $bankAccount,
            'import' => $import,
            'original_headers' => $import->original_headers ?? [],
        ]);
    }

    /**
     * Update the CSV column mapping for a specific import and re-process staged transactions.
     */
    public function updateMapping(UpdateBankStatementMappingRequest $request, BankAccount $bankAccount, BankStatementImport $import): RedirectResponse
    {
        $validatedData = $request->validated();

        if (! $import->original_file_path || ! Storage::disk('local')->exists($import->original_file_path)) {
            Log::error("Original file not found for import ID: {$import->id} at path: {$import->original_file_path}");

            return redirect()->route('bank-accounts.import.mapping.show', ['bankAccount' => $bankAccount->id, 'import' => $import->id])
                ->with('error', 'Original CSV file not found. Cannot re-process.');
        }

        $newColumnMapping = [
            'transaction_date' => $validatedData['transaction_date_column'],
            'description' => $validatedData['description_column'] ?? null,
            'amount_type' => $validatedData['amount_type'],
            'amount' => $validatedData['amount_type'] === 'single' ? $validatedData['amount_column'] : null,
            'debit_amount' => $validatedData['amount_type'] === 'separate' ? $validatedData['debit_amount_column'] : null,
            'credit_amount' => $validatedData['amount_type'] === 'separate' ? $validatedData['credit_amount_column'] : null,
        ];

        DB::beginTransaction();
        try {
            $import->column_mapping = $newColumnMapping;
            $import->status = 'processing';
            $import->save();

            $import->stagedTransactions()->delete();

            $csv = Reader::createFromPath(Storage::disk('local')->path($import->original_file_path), 'r');
            $csv->setHeaderOffset(0);
            $records = Statement::create()->process($csv);

            $stagedCount = 0;
            $errorCount = 0;
            $dateWindowDays = 2;

            foreach ($records as $record) {
                $rawDate = $record[$newColumnMapping['transaction_date']] ?? null;
                $rawDescription = $newColumnMapping['description'] ? ($record[$newColumnMapping['description']] ?? '') : '';

                $parsedAmount = null;
                $amountParseError = false;

                if ($newColumnMapping['amount_type'] === 'single') {
                    $rawAmount = $newColumnMapping['amount'] ? ($record[$newColumnMapping['amount']] ?? null) : null;
                    if ($rawAmount !== null) {
                        $parsedAmount = $this->parseAmount($rawAmount);
                        if ($parsedAmount === null) {
                            $amountParseError = true;
                        }
                    } else {
                        $amountParseError = true;
                    }
                } else { // separate debit/credit
                    $rawDebit = $newColumnMapping['debit_amount'] ? ($record[$newColumnMapping['debit_amount']] ?? null) : null;
                    $rawCredit = $newColumnMapping['credit_amount'] ? ($record[$newColumnMapping['credit_amount']] ?? null) : null;

                    $hasDebitValue = $rawDebit !== null && trim($rawDebit) !== '';
                    $hasCreditValue = $rawCredit !== null && trim($rawCredit) !== '';

                    if ($hasDebitValue && $hasCreditValue) {
                        Log::warning("Row has both debit and credit values for import {$import->id}: ", $record);
                        $amountParseError = true;
                    } elseif ($hasDebitValue) {
                        $parsedDebit = $this->parseAmount($rawDebit);
                        if ($parsedDebit !== null) {
                            $parsedAmount = -$parsedDebit;
                        } else {
                            $amountParseError = true;
                        }
                    } elseif ($hasCreditValue) {
                        $parsedCredit = $this->parseAmount($rawCredit);
                        if ($parsedCredit !== null) {
                            $parsedAmount = $parsedCredit;
                        } else {
                            $amountParseError = true;
                        }
                    } else {
                        $amountParseError = true;
                    }
                }

                $parsedDate = $this->parseDate($rawDate);

                if ($parsedDate === null || $amountParseError) {
                    Log::warning("Skipping row due to parsing error (date or amount) for import {$import->id}: ", $record);
                    $errorCount++;

                    continue;
                }

                if ($parsedAmount === null) {
                    Log::warning("Skipping row due to null amount despite no explicit parse error for import {$import->id}: ", $record);
                    $errorCount++;

                    continue;
                }

                $stagedData = [
                    'user_id' => Auth::id(),
                    'bank_account_id' => $bankAccount->id,
                    'bank_statement_import_id' => $import->id,
                    'transaction_date' => $parsedDate,
                    'description' => Str::limit(trim($rawDescription), 255),
                    'amount' => $parsedAmount,
                    'original_raw_data' => json_encode($record),
                    'data_hash' => md5(json_encode($record)),
                    'status' => 'pending_review',
                ];

                $potentialDuplicate = Transaction::where('user_id', Auth::id())
                    ->where('bank_account_id', $bankAccount->id)
                    ->where('amount', $parsedAmount)
                    ->whereBetween('transaction_date', [
                        Carbon::parse($parsedDate)->subDays($dateWindowDays)->toDateString(),
                        Carbon::parse($parsedDate)->addDays($dateWindowDays)->toDateString(),
                    ])
                    ->first();

                if ($potentialDuplicate) {
                    $stagedData['status'] = 'potential_duplicate';
                    $stagedData['matched_transaction_id'] = $potentialDuplicate->id;
                }

                StagedTransaction::create($stagedData);
                $stagedCount++;
            }

            if ($stagedCount === 0 && $errorCount > 0) {
                DB::rollBack();
                Log::error("All rows failed to parse after re-mapping for import ID: {$import->id}");

                return redirect()->route('bank-accounts.import.mapping.show', ['bankAccount' => $bankAccount->id, 'import' => $import->id])
                    ->with('error', 'Failed to re-process transactions. All rows resulted in parsing errors with the new mapping. Please review your mapping and CSV data.');
            }

            $import->status = $stagedCount > 0 ? 'awaiting_review' : 'failed_processing';
            $import->save();

            DB::commit();

            $message = $stagedCount > 0 ? 'Column mapping updated and transactions re-staged successfully.' : 'Column mapping updated, but no valid transactions were found to stage.';
            if ($errorCount > 0) {
                $message .= " {$errorCount} row(s) could not be parsed.";
            }

            return redirect()->route('bank-accounts.staged.review', $bankAccount)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating column mapping for import {$import->id}: ".$e->getMessage().' on line '.$e->getLine().' in '.$e->getFile());

            return redirect()->route('bank-accounts.import.mapping.show', ['bankAccount' => $bankAccount->id, 'import' => $import->id])
                ->with('error', 'An unexpected error occurred while updating the mapping: '.$e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', BankAccount::class);

        return view('bank-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $this->authorize('create', BankAccount::class);

        $validatedData = $request->validated();
        $validatedData['user_id'] = Auth::id();

        // Calculate current balance if opening balance is provided
        if (isset($validatedData['opening_balance'])) {
            $validatedData['current_balance'] = $validatedData['opening_balance'];
        }

        try {
            BankAccount::create($validatedData);

            return redirect()->route('bank-accounts.index')
                ->with('success', 'Bank account created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating bank account: '.$e->getMessage());

            return redirect()->route('bank-accounts.create')
                ->with('error', 'Failed to create bank account. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount): \Illuminate\Contracts\View\View
    {
        $this->authorize('view', $bankAccount);

        // Eager load transactions for display, ordered by date then ID
        $bankAccount->load(['transactions' => function ($query) {
            $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
        }]);

        // Count pending staged transactions for review
        $pendingStagedCount = StagedTransaction::where('bank_account_id', $bankAccount->id)
            ->whereIn('status', ['pending_review', 'potential_duplicate'])
            ->count();

        return view('bank-accounts.show', compact('bankAccount', 'pendingStagedCount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): View
    {
        $this->authorize('update', $bankAccount);

        return view('bank-accounts.edit', compact('bankAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('update', $bankAccount);

        $validatedData = $request->validated();

        try {
            $bankAccount->update($validatedData);

            return redirect()->route('bank-accounts.index')
                ->with('success', 'Bank account updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating bank account {$bankAccount->id}: ".$e->getMessage());

            return redirect()->route('bank-accounts.edit', $bankAccount)
                ->with('error', 'Failed to update bank account. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('delete', $bankAccount);

        // Basic deletion. Add checks for transactions or other dependencies if needed.
        // For example, if an account has transactions, you might prevent deletion
        // or require a confirmation step.

        try {
            $bankAccount->delete();

            return redirect()->route('bank-accounts.index')
                ->with('success', 'Bank account deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting bank account {$bankAccount->id}: ".$e->getMessage());

            return redirect()->route('bank-accounts.index')
                ->with('error', 'Failed to delete bank account. Please try again.');
        }
    }

    /**
     * Show the form for creating a new withdrawal for a bank account.
     */
    public function createWithdrawal(BankAccount $bankAccount): Response
    {
        $this->authorize('update', $bankAccount); // Use 'update' policy for now, can be refined

        // TODO: Return a view for the withdrawal creation form
        // Example: return view('bank-accounts.withdrawals.create', compact('bankAccount'));

        return response("Placeholder for create withdrawal form for bank account: ID {$bankAccount->id} - {$bankAccount->account_name}");
    }

    /**
     * Parse a raw amount string into a float.
     */
    private function parseAmount(?string $rawAmount): ?float
    {
        if ($rawAmount === null || trim($rawAmount) === '') {
            return null;
        }
        // Remove currency symbols, thousands separators (commas), and leading/trailing whitespace
        $cleanedAmount = preg_replace('/[^\d.-]/', '', $rawAmount);
        if (! is_numeric($cleanedAmount)) {
            return null;
        }

        return (float) $cleanedAmount;
    }

    /**
     * Parse a raw date string into a standard Y-m-d format or Carbon object.
     *
     * @return string|null Carbon object or Y-m-d string, null on failure
     */
    private function parseDate(?string $rawDate): ?string // Or ?Carbon
    {
        if ($rawDate === null || trim($rawDate) === '') {
            return null;
        }
        try {
            // Attempt common date formats. Add more as needed.
            // Order matters: more specific or common formats first.
            $formats = [
                'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d',
                'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
                'm/d/Y H:i:s', 'm/d/Y H:i', 'm/d/Y',
                'Y.m.d', 'd.m.Y', 'm.d.Y',
                // Add formats like 'd-M-Y' if your CSV might contain them
            ];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $rawDate)->toDateString();
                } catch (\InvalidArgumentException $e) {
                    // Try next format
                }
            }

            // Fallback for ISO 8601 or other Carbon-parsable strings
            return Carbon::parse($rawDate)->toDateString();
        } catch (\Exception $e) {
            Log::warning("Date parsing failed for '{$rawDate}': ".$e->getMessage());

            return null;
        }
    }
}
