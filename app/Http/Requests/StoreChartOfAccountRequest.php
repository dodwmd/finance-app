<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can create accounts
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        $accountTypes = ['asset', 'liability', 'equity', 'revenue', 'expense', 'costofgoodssold'];

        return [
            'account_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in($accountTypes)],
            'description' => ['nullable', 'string'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'is_active' => ['boolean'],
            'allow_direct_posting' => ['boolean'],
            'system_account_tag' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
                // Ensure system_account_tag is unique for the user if set, ignoring current record on update
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    #[\Override]
    protected function prepareForValidation(): void
    {
        if ($this->has('type')) {
            $this->merge([
                'type' => strtolower($this->input('type')),
            ]);
        }

        // Ensure boolean fields are correctly cast or defaulted
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'allow_direct_posting' => $this->has('allow_direct_posting') ? $this->boolean('allow_direct_posting') : true,
        ]);

        if ($this->input('parent_id') === '') {
            $this->merge(['parent_id' => null]);
        }
    }
}
