<?php

namespace App\Http\Controllers;

class BankStatementImportController extends Controller
{
    /**
     * Show the mapping UI for a pending BankStatementImport.
     */
    public function showMapping($bankAccountId, $importId)
    {
        $bankAccount = \App\Models\BankAccount::findOrFail($bankAccountId);
        $import = \App\Models\BankStatementImport::findOrFail($importId);

        // Authorize (ensure the user owns the account or has permission)
        $this->authorize('view', $bankAccount);
        // Check import belongs to this account
        if ($import->bank_account_id !== $bankAccount->id) {
            abort(403, 'This import does not belong to the specified bank account.');
        }
        // Check status
        if ($import->status !== 'pending_mapping') {
            abort(400, 'This import is not pending mapping.');
        }

        return view('bank-accounts.import.mapping', [
            'bankAccount' => $bankAccount,
            'import' => $import,
        ]);
    }

    /**
     * Update the mapping for a BankStatementImport and re-process staged transactions.
     */
    public function updateMapping($bankAccountId, $importId, \Illuminate\Http\Request $request)
    {
        $bankAccount = \App\Models\BankAccount::findOrFail($bankAccountId);
        $import = \App\Models\BankStatementImport::findOrFail($importId);
        $this->authorize('update', $bankAccount);
        if ($import->bank_account_id !== $bankAccount->id) {
            abort(403, 'This import does not belong to the specified bank account.');
        }
        if ($import->status !== 'pending_mapping') {
            abort(400, 'This import is not pending mapping.');
        }

        // Validate user mapping input
        $validated = $request->validate([
            'transaction_date_column' => 'required|string',
            'description_column' => 'nullable|string',
            'amount_type' => 'required|in:single,separate',
            'amount_column' => 'required_if:amount_type,single',
            'debit_column' => 'required_if:amount_type,separate',
            'credit_column' => 'required_if:amount_type,separate',
        ]);

        // Save mapping to import
        $columnMapping = [
            'transaction_date' => $validated['transaction_date_column'],
            'description' => $validated['description_column'] ?? null,
            'amount_type' => $validated['amount_type'],
            'amount' => $validated['amount_column'] ?? null,
            'debit_amount' => $validated['debit_column'] ?? null,
            'credit_amount' => $validated['credit_column'] ?? null,
        ];
        $import->column_mapping = $columnMapping;
        $import->status = 'awaiting_review';
        $import->save();

        // Remove previously staged transactions for this import
        \App\Models\StagedTransaction::where('bank_statement_import_id', $import->id)->delete();

        // Re-parse CSV and stage transactions
        // The original CSV path should be stored in the import (assume $import->csv_path)
        if (! isset($import->csv_path) || ! \Illuminate\Support\Facades\Storage::exists($import->csv_path)) {
            return redirect()->back()->withErrors(['csv' => 'Original CSV file not found. Cannot re-process import.']);
        }
        $csvStream = \Illuminate\Support\Facades\Storage::readStream($import->csv_path);
        $csv = \League\Csv\Reader::createFromStream($csvStream);
        $csv->setHeaderOffset(0);
        $records = \League\Csv\Statement::create()->process($csv);

        $userId = $bankAccount->user_id;
        $dateColumn = $columnMapping['transaction_date'];
        $descriptionColumn = $columnMapping['description'];
        $amountType = $columnMapping['amount_type'];
        $amountColumn = $columnMapping['amount'];
        $debitColumn = $columnMapping['debit_amount'];
        $creditColumn = $columnMapping['credit_amount'];

        $successCount = 0;
        $errorCount = 0;
        foreach ($records as $record) {
            $rawDate = $record[$dateColumn] ?? null;
            $rawDescription = $descriptionColumn ? ($record[$descriptionColumn] ?? '') : '';
            $parsedAmount = null;
            $amountParseError = false;
            if ($amountType === 'single') {
                $rawAmount = $amountColumn ? ($record[$amountColumn] ?? null) : null;
                if ($rawAmount !== null) {
                    $parsedAmount = floatval(str_replace([',', ' '], ['', ''], $rawAmount));
                } else {
                    $amountParseError = true;
                }
            } else {
                $debit = $debitColumn ? ($record[$debitColumn] ?? null) : null;
                $credit = $creditColumn ? ($record[$creditColumn] ?? null) : null;
                if ($debit !== null && is_numeric($debit)) {
                    $parsedAmount = -1 * floatval(str_replace([',', ' '], ['', ''], $debit));
                } elseif ($credit !== null && is_numeric($credit)) {
                    $parsedAmount = floatval(str_replace([',', ' '], ['', ''], $credit));
                } else {
                    $amountParseError = true;
                }
            }
            // Parse date
            try {
                $parsedDate = $rawDate ? \Carbon\Carbon::parse($rawDate) : null;
            } catch (\Exception $e) {
                $parsedDate = null;
            }
            if ($parsedDate && ! $amountParseError && $parsedAmount !== null) {
                \App\Models\StagedTransaction::create([
                    'user_id' => $userId,
                    'bank_account_id' => $bankAccount->id,
                    'bank_statement_import_id' => $import->id,
                    'transaction_date' => $parsedDate,
                    'description' => $rawDescription,
                    'amount' => $parsedAmount,
                    'status' => 'pending_review',
                ]);
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return redirect()->route('bank-accounts.staged.review', $bankAccount->id)
            ->with('success', "Mapping updated. {$successCount} transactions staged. {$errorCount} errors.");
    }
}
