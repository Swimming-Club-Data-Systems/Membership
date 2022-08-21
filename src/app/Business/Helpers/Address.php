<?php

namespace App\Business\Helpers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    return json_encode([
      'streetAndNumber' => $this->address_line_1,
      'city' => $this->city,
      'postCode' => $this->post_code,
      'county' => $this->county,
      'country' => $this->country_code,
    ]);
  }

  public static function validationRules()
  {
    return [
      'address_line_1' => 'required|max:255',
      'city' => 'required|max:255',
      'county' => 'required|max:255',
      'post_code' => 'required|max:255',
      'country' => ['required', 'max:2', Rule::in(Countries::getISOKeys())],
    ];
  }
}
