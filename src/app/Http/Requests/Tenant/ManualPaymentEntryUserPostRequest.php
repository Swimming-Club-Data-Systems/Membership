<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\ManualPaymentEntry;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualPaymentEntryUserPostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        /** @var ManualPaymentEntry $entry */
        $entry = $this->entry;

        return [
            'user_select' => [
                'required',
                'integer',
                Rule::exists('users', 'UserID')->where(function (Builder $query) {
                    return $query->where('Active', true)->where('Tenant', tenant('id'));
                }),
                Rule::unique('manual_payment_entry_user', 'user_UserID')->where(function (Builder $query) use ($entry) {
                    return $query->where('manual_payment_entry_id', $entry->id);
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
            'user_select.required' => 'A user is required',
            'user_select.integer' => 'The provided user id must be an integer',
            'user_select.exists:users,UserID' => 'A user is required',
            'user_select.unique' => 'The selected user is already assigned to this manual payment entry',
        ];
    }
}
