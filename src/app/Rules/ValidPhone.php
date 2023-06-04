<?php

namespace App\Rules;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;

class ValidPhone implements InvokableRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $number = null;

        $country = 'GB';
        if (isset($this->data['phone_country'])) {
            $country = $this->data['phone_country'];
        }

        try {
            $number = PhoneNumber::parse($value, $country);
        } catch (PhoneNumberParseException $e) {
            // 'The string supplied is too short to be a phone number.'
            $fail('The :attribute is not a valid phone number.');
        }

        if (! $number->isValidNumber()) {
            // strict check relying on up-to-date metadata library
            $fail('The :attribute is not a valid phone number.');
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
