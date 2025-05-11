<?php

namespace App\Http\Requests;

use App\Models\BankAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the bankAccount belongs to the authenticated user
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id,user_id,'.Auth::id().',type,expense'], // Optional: allow categorizing withdrawal, ensure category is an expense type and belongs to user
        ];
    }
}
