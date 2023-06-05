<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompetitionSessionPutRequest extends FormRequest
{
    protected $errorBag = 'edit_session';

    //    /**
    //     * Determine if the user is authorized to make this request.
    //     */
    //    public function authorize(): bool
    //    {
    //        //        return false;
    //    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        //        $this->merge([
        //            'start_time' => $this->string('start_date').$this->string('start_time'),
        //            'end_time' => $this->string('end_date').$this->string('end_time'),
        //        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:255',
            ],
            'venue' => [
                'required',
                Rule::exists('venues', 'id')->where(function (Builder $query) {
                    return $query->where('Tenant', tenant('id'));
                }),
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start',
            ],
        ];
    }
}
