<?php

namespace App\Http\Requests;

use App\Models\BankAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateBankAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the user owns the bank account they are trying to update
        $bankAccount = $this->route('bank_account');

        // Check if $bankAccount is an instance of BankAccount and then check user_id
        return $bankAccount instanceof BankAccount && $bankAccount->user_id == Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        $bankAccount = $this->route('bank_account');

        // Ensure $bankAccount is an instance of BankAccount before accessing id
        $bankAccountId = $bankAccount instanceof BankAccount ? $bankAccount->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['bank', 'credit_card', 'cash'])],
            'account_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('bank_accounts')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })->ignore($bankAccountId),
            ],
            // BSB can be nullable. Add specific validation if needed (e.g., format).
            'bsb' => ['nullable', 'string', 'max:20'],
            // Opening balance should generally not be editable after creation.
            // If it needs to be adjusted, it should be done via a specific journal entry or adjustment transaction.
            // 'opening_balance' => ['sometimes', 'numeric', 'min:0'], // Not typically editable
        ];
    }
}
