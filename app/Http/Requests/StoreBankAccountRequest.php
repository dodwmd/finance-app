<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allows any authenticated user to attempt creation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id(); // Capture Auth::id() here

        return [
            'account_name' => ['required', 'string', 'max:255'], // This is the user-facing account name
            'type' => ['required', 'string', Rule::in(['bank', 'credit_card', 'cash'])], // This is the broad type
            'account_type' => ['nullable', 'string', 'max:100'], // More specific type like 'chequing', 'savings'
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'account_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('bank_accounts')->where(function ($query) use ($userId) { // Pass $userId via `use`
                    return $query->where('user_id', $userId);
                }),
            ],
            'bsb' => ['nullable', 'string', 'digits:6'],
            'currency' => ['nullable', 'string', 'max:10'], // e.g., CAD, USD
            'opening_balance' => ['required', 'numeric', 'min:0'],
            // 'is_active' will likely be handled by default in controller or model
        ];
    }
}
