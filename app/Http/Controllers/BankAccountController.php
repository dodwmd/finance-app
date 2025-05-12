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
use Illuminate\Http\Request;
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
     *
     * Shows a form allowing users to manually map CSV columns to transaction fields
     * when automatic detection was insufficient. The form displays original CSV headers
     * and allows users to select the appropriate columns for transaction date, description,
     * and amount fields (either single amount or separate debit/credit columns).
     *
     * @param  BankAccount  $bankAccount  The bank account the import belongs to
     * @param  BankStatementImport  $import  The import record needing column mapping
     * @return View The column mapping form view
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException If user is not authorized
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
     *
     * Processes the user-defined column mapping, updates the BankStatementImport record,
     * deletes any previously staged transactions for this import, and re-processes the
     * original CSV file with the new mapping. Creates new staged transactions based on
     * the updated mapping and handles duplicate detection.
     *
     * @param  UpdateBankStatementMappingRequest  $request  Validated request with mapping data
     * @param  BankAccount  $bankAccount  The bank account the import belongs to
     * @param  BankStatementImport  $import  The import record to update
     * @return RedirectResponse Redirect to review page or back to mapping form with errors
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException If user is not authorized
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
     * Show the form for importing a CSV bank statement.
     *
     * Displays a form where users can upload CSV bank statements for import.
     *
     * @param  BankAccount  $bankAccount  The bank account to import transactions for
     * @return View The CSV import form view
     */
    public function showImportForm(BankAccount $bankAccount): View
    {
        $this->authorize('update', $bankAccount);

        return view('bank-accounts.import.create', compact('bankAccount'));
    }

    /**
     * Process the imported CSV file, detect columns, and create a BankStatementImport.
     *
     * This method handles the CSV file upload, attempts to auto-detect column mappings,
     * creates a BankStatementImport record, and either:
     * 1. Redirects to the column mapping form if essential columns cannot be detected, or
     * 2. Processes the transactions and redirects to the review page if mappings are successful
     *
     * The process includes:
     * - CSV parsing and header detection
     * - Intelligent column mapping based on common header names
     * - Transaction date, description, and amount field detection
     * - Support for both single amount columns and separate debit/credit columns
     * - Duplicate transaction detection
     *
     * @param  Request  $request  The HTTP request containing the uploaded file
     * @param  BankAccount  $bankAccount  The bank account to import transactions for
     * @return RedirectResponse Redirect to either mapping form or review page
     */
    public function storeImport(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('update', $bankAccount);

        $request->validate([
            'statement_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('statement_file');
            $originalFilename = $file->getClientOriginalName();
            $filePath = $file->store('bank_statements');

            // Parse the CSV headers
            $csv = Reader::createFromPath(Storage::disk('local')->path($filePath), 'r');
            $csv->setHeaderOffset(0);
            $csvHeaders = $csv->getHeader();

            // Create the bank statement import record
            $import = new BankStatementImport([
                'user_id' => Auth::id(),
                'bank_account_id' => $bankAccount->id,
                'original_filename' => $originalFilename,
                'original_file_path' => $filePath,
                'original_headers' => $csvHeaders,
                'status' => 'processing',
            ]);

            // Attempt to automatically detect column mappings
            $dateColumn = null;
            $descriptionColumn = null;
            $amountColumn = null;
            $debitColumn = null;
            $creditColumn = null;

            // Search for date columns
            foreach ($csvHeaders as $header) {
                $lower = strtolower($header);

                // Date detection
                if (strpos($lower, 'date') !== false || strpos($lower, 'time') !== false) {
                    $dateColumn = $header;
                }

                // Description detection
                if (strpos($lower, 'desc') !== false || strpos($lower, 'narration') !== false ||
                    strpos($lower, 'particular') !== false || strpos($lower, 'detail') !== false) {
                    $descriptionColumn = $header;
                }

                // Amount detection
                if ($lower === 'amount' || strpos($lower, 'amount') !== false) {
                    $amountColumn = $header;
                }

                // Debit/Credit detection
                if (strpos($lower, 'debit') !== false || strpos($lower, 'withdraw') !== false) {
                    $debitColumn = $header;
                }

                if (strpos($lower, 'credit') !== false || strpos($lower, 'deposit') !== false) {
                    $creditColumn = $header;
                }
            }

            // Determine amount type (single or separate debit/credit)
            $amountType = null;
            if ($amountColumn) {
                $amountType = 'single';
            } elseif ($debitColumn || $creditColumn) {
                $amountType = 'separate';
            }

            // Set the detected column mapping
            $columnMapping = [
                'transaction_date' => $dateColumn,
                'description' => $descriptionColumn,
                'amount_type' => $amountType,
                'amount' => $amountColumn,
                'debit_amount' => $debitColumn,
                'credit_amount' => $creditColumn,
            ];

            $import->column_mapping = $columnMapping;

            // If essential columns are missing, set status to pending_mapping
            if (! $dateColumn || (! $amountColumn && (! $debitColumn && ! $creditColumn))) {
                $import->status = 'pending_mapping';
                $import->save();

                return redirect()->route('bank-accounts.import.mapping.show', [
                    'bankAccount' => $bankAccount->id,
                    'import' => $import->id,
                ])->with('info', 'We couldn\'t automatically detect all required columns. Please map the columns manually.');
            }

            // Otherwise process the import
            $import->status = 'awaiting_review';
            $import->save();

            // Process the records with the detected mapping
            $records = Statement::create()->process($csv);
            $successCount = 0;
            $errorCount = 0;
            $dateWindowDays = 2;

            foreach ($records as $record) {
                $rawDate = isset($record[$dateColumn]) ? $record[$dateColumn] : null;
                $rawDescription = $descriptionColumn ? ($record[$descriptionColumn] ?? '') : '';

                $parsedAmount = null;

                if ($amountType === 'single') {
                    $rawAmount = $amountColumn ? ($record[$amountColumn] ?? null) : null;
                    if ($rawAmount !== null) {
                        $parsedAmount = $this->parseAmount($rawAmount);
                    }
                } else { // separate debit/credit
                    $rawDebit = $debitColumn ? ($record[$debitColumn] ?? null) : null;
                    $rawCredit = $creditColumn ? ($record[$creditColumn] ?? null) : null;

                    $hasDebitValue = $rawDebit !== null && trim($rawDebit) !== '';
                    $hasCreditValue = $rawCredit !== null && trim($rawCredit) !== '';

                    if ($hasDebitValue) {
                        $parsedDebit = $this->parseAmount($rawDebit);
                        if ($parsedDebit !== null) {
                            $parsedAmount = -$parsedDebit; // Make debit negative
                        }
                    } elseif ($hasCreditValue) {
                        $parsedCredit = $this->parseAmount($rawCredit);
                        if ($parsedCredit !== null) {
                            $parsedAmount = $parsedCredit; // Keep credit positive
                        }
                    }
                }

                $parsedDate = $this->parseDate($rawDate);

                if ($parsedDate === null || $parsedAmount === null) {
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

                // Check for potential duplicates
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
                $successCount++;
            }

            if ($successCount === 0) {
                return redirect()->route('bank-accounts.import.mapping.show', [
                    'bankAccount' => $bankAccount->id,
                    'import' => $import->id,
                ])->with('warning', 'No valid transactions were found. Please check your CSV file and update the column mapping.');
            }

            return redirect()->route('bank-accounts.staged.review', $bankAccount)
                ->with('success', "Successfully processed {$successCount} transactions".
                    ($errorCount > 0 ? " with {$errorCount} errors." : '.'));

        } catch (\Exception $e) {
            Log::error('CSV import error: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'bank_account_id' => $bankAccount->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('bank-accounts.import.form', $bankAccount)
                ->with('error', 'Failed to process the CSV file: '.$e->getMessage());
        }
    }

    /**
     * Method to display staged transactions for review
     */
    public function reviewStagedTransactions(BankAccount $bankAccount): View
    {
        $this->authorize('view', $bankAccount);

        $stagedTransactions = StagedTransaction::where('user_id', Auth::id())
            ->where('bank_account_id', $bankAccount->id)
            ->whereIn('status', ['pending_review', 'potential_duplicate'])
            ->whereHas('bankStatementImport', function ($query) {
                $query->whereIn('status', ['awaiting_review', 'partial_duplicate', 'failed_processing']);
            })
            ->with('bankStatementImport') // Eager load the relationship
            ->orderBy('transaction_date', 'asc')
            ->paginate(50);

        // If you need the list of import objects for the view separately:
        $importIds = $stagedTransactions->pluck('bank_statement_import_id')->unique()->toArray();
        $imports = BankStatementImport::whereIn('id', $importIds)->orderBy('created_at', 'desc')->get();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $categories = $user->categories()->orderBy('name')->get(); // Fetch categories

        return view('bank-accounts.staged.review', [
            'bankAccount' => $bankAccount,
            'stagedTransactions' => $stagedTransactions,
            'imports' => $imports,
            'categories' => $categories, // Pass categories to the view
        ]);
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
