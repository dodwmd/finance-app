<?php

namespace App\Http\Requests;

use App\Models\BankAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreBankStatementImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $bankAccount = $this->route('bankAccount');

        return $bankAccount instanceof BankAccount && $bankAccount->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'statement_file' => ['required', 'file', 'mimes:csv,txt'], // Allowing txt for CSVs with .txt extension
        ];
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
            'statement_file.required' => 'Please select a statement file to upload.',
            'statement_file.file' => 'The uploaded item is not a valid file.',
            'statement_file.mimes' => 'The statement file must be a CSV (.csv or .txt). QIF/OFX support coming soon.',
        ];
    }
}
