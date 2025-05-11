<?php

namespace App\Http\Requests;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateChartOfAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the user owns the chart of account or is an admin, etc.
        // For now, just checking if authenticated and owns the record.
        $chartOfAccount = $this->route('chart_of_account');

        return Auth::check() && $chartOfAccount instanceof ChartOfAccount && $chartOfAccount->user_id == Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        $accountId = $this->chart_of_account instanceof ChartOfAccount ? $this->chart_of_account->id : null;
        if (is_null($accountId) && $this->route('chart_of_account') instanceof ChartOfAccount) {
            $accountId = $this->route('chart_of_account')->id;
        } elseif (is_string($this->route('chart_of_account'))) {
            $accountId = (int) $this->route('chart_of_account');
        }

        $accountTypes = ['asset', 'liability', 'equity', 'revenue', 'expense', 'costofgoodssold'];

        return [
            'account_code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })->ignore($accountId),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', Rule::in($accountTypes)],
            'description' => ['nullable', 'string'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
                Rule::notIn(is_null($accountId) ? [] : [$accountId]),
            ],
            'is_active' => ['boolean'],
            'allow_direct_posting' => ['boolean'],
            'system_account_tag' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })->ignore($accountId),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'allow_direct_posting' => $this->has('allow_direct_posting') ? $this->boolean('allow_direct_posting') : true,
        ]);

        if ($this->has('type')) {
            $this->merge([
                'type' => strtolower($this->input('type')),
            ]);
        }

        if ($this->input('parent_id') === '') {
            $this->merge(['parent_id' => null]);
        }
    }
}
