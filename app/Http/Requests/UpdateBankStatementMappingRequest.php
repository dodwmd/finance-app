<?php

namespace App\Http\Requests;

use App\Models\BankStatementImport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankStatementMappingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by checking ownership of the BankAccount and BankStatementImport in the controller.
        // Or, we can do it here if we fetch the $import model via route.
        $import = $this->route('import'); // Assumes 'import' is the route parameter name
        $bankAccount = $this->route('bankAccount'); // Assumes 'bankAccount' is the route parameter name

        if (! $import instanceof \App\Models\BankStatementImport || ! $bankAccount instanceof \App\Models\BankAccount) {
            return false;
        }

        return $this->user()->id === $import->user_id && $this->user()->id === $bankAccount->user_id && $import->bank_account_id === $bankAccount->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $import = $this->route('import');
        if (! $import instanceof BankStatementImport) {
            // This should ideally not happen if route model binding and authorize work correctly
            // but as a fallback, return empty rules or throw an exception.
            return [];
        }
        $csvHeaders = $import->original_headers ?? [];

        $rules = [
            'transaction_date_column' => ['required', 'string', Rule::in($csvHeaders)],
            'description_column' => ['nullable', 'string', Rule::in($csvHeaders)],
            'amount_type' => ['required', 'string', Rule::in(['single', 'separate'])],
        ];

        if ($this->input('amount_type') === 'single') {
            $rules['amount_column'] = ['required', 'string', Rule::in($csvHeaders)];
            // Ensure other amount fields are not present or are ignored if 'single' is chosen
            $rules['debit_amount_column'] = ['prohibited_if:amount_type,single'];
            $rules['credit_amount_column'] = ['prohibited_if:amount_type,single'];
        } elseif ($this->input('amount_type') === 'separate') {
            $rules['debit_amount_column'] = ['required', 'string', Rule::in($csvHeaders)];
            $rules['credit_amount_column'] = ['required', 'string', Rule::in($csvHeaders), 'different:debit_amount_column'];
            // Ensure single amount field is not present or is ignored if 'separate' is chosen
            $rules['amount_column'] = ['prohibited_if:amount_type,separate'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'transaction_date_column.required' => 'The transaction date column mapping is required.',
            'transaction_date_column.in' => 'The selected transaction date column is not a valid header from your CSV.',
            'description_column.in' => 'The selected description column is not a valid header from your CSV.',
            'amount_type.required' => 'Please specify how amounts are represented (single column or separate debit/credit).',
            'amount_type.in' => 'Invalid amount representation type selected.',
            'amount_column.required' => 'The amount column is required when using single amount mode.',
            'amount_column.in' => 'The selected amount column is not a valid header from your CSV.',
            'debit_amount_column.required' => 'The debit amount column is required when using separate debit/credit mode.',
            'debit_amount_column.in' => 'The selected debit amount column is not a valid header from your CSV.',
            'credit_amount_column.required' => 'The credit amount column is required when using separate debit/credit mode.',
            'credit_amount_column.in' => 'The selected credit amount column is not a valid header from your CSV.',
            'credit_amount_column.different' => 'The credit amount column must be different from the debit amount column.',
            '*.prohibited_if' => 'This field should not be provided with the current amount type selection.',
        ];
    }
}
