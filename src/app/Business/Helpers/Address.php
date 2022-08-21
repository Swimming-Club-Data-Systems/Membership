<?php

namespace App\Business\Helpers;

use App\Rules\ValidPostCode;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Brick\Postcode\PostcodeFormatter;
use Brick\Postcode\UnknownCountryException;
use Brick\Postcode\InvalidPostcodeException;

/**
 * Standard address helper
 */
class Address
{
  public $address_line_1;
  public $city;
  public $post_code;
  public $county;
  public $country_code;
  public $country_name;

  public static function create(?string $json)
  {
    $object = new self;

    // Try to decode
    try {
      $json = json_decode($json);
    } catch (\Exception $e) {
      // Ignore
    }

    $object->address_line_1 = $json?->streetAndNumber;
    $object->city = $json?->city;
    $object->post_code = $json?->postCode;
    $object->county = $json?->county;
    $object->country_code = $json?->country ?? "GB";
    $object->country_name = Countries::getCountryName($object->country_code);

    return $object;
  }

  public function __toString()
  {
    $postCodeFormatter = new PostcodeFormatter();
    $postCode = null;

    try {
      $postCode = $postCodeFormatter->format($this->country_code, $this->post_code);
    } catch (UnknownCountryException | InvalidPostcodeException $e) {
      $postCode = "";
    }

    return json_encode([
      'streetAndNumber' => $this->address_line_1,
      'city' => $this->city,
      'county' => $this->county,
      'country' => $this->country_code,
      'postCode' => $postCode,
    ]);
  }

  public static function validationRules()
  {
    return [
      'address_line_1' => 'required|max:255',
      'city' => 'required|max:255',
      'county' => 'required|max:255',
      'post_code' => ['required', 'max:255', new ValidPostCode],
      'country' => ['required', 'max:2', Rule::in(Countries::getISOKeys())],
    ];
  }
}
