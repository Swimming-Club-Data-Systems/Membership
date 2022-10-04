<?php

namespace App\Rules;

use App\Business\Helpers\Countries;
use Illuminate\Contracts\Validation\InvokableRule;
use Brick\Postcode\PostcodeFormatter;
use Brick\Postcode\UnknownCountryException;
use Brick\Postcode\InvalidPostcodeException;
use Illuminate\Contracts\Validation\DataAwareRule;

class ValidPostCode implements InvokableRule, DataAwareRule
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
        $postCodeFormatter = new PostcodeFormatter();

        $country = 'GB';
        if (isset($this->data['country'])) {
            $country = $this->data['country'];
        }

        try {
            $postCodeFormatter->format($country, $value);
        } catch (UnknownCountryException $e) {
            // Country may not support post codes, can ignore this exception
        } catch (InvalidPostcodeException $e) {
            $fail('The postal code you entered is not a valid ' . Countries::getCountryName($country) . ' postal code.');
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
