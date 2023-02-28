<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\ManualPaymentEntry;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualPaymentEntryLinePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'description' => [
                'required',
                'max:255',
            ],
            'amount' => [
                'required',
                'decimal:0,2',
                'max:1000',
                'min:0',
            ],
            'type' => [
                Rule::in(['credit', 'debit']),
            ],
            'journal_select' => [
                'required',
                'integer',
                Rule::exists('journal_accounts', 'id')->where(function (Builder $query) {
                    return $query->where('Tenant', tenant('id'));
                }),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'The amount must be more than or equal to than £:min.',
            'amount.max' => 'The amount must be less than or equal to £:max.',
            'journal_select.required' => 'A journal account is required.',
            'journal_select.integer' => 'The provided journal account id must be an integer.',
            'journal_select.exists:journal_accounts,id' => 'A journal account required.',
        ];
    }
}
