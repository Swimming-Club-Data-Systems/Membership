<?php

namespace App\Business\Helpers;

use App\Rules\ValidPhone;
use Brick\PhoneNumber\PhoneNumber as BrickPhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Illuminate\Validation\Rule;

/**
 * Formats and deals with phone numbers
 */
class PhoneNumber
{
  protected $number;

  protected function __construct($number)
  {
    $this->number = $number;
  }

  /**
   * Create from stored value
   */
  public static function create(string $number)
  {
    $numberObj = BrickPhoneNumber::parse($number);

    return new self($numberObj);
  }

  public static function toDatabaseFormat(string $number, string $country = "GB")
  {
    try {
      $number = BrickPhoneNumber::parse($number, $country);
    } catch (PhoneNumberParseException $e) {
      // 'The string supplied is too short to be a phone number.'
      return "";
    }

    if (!$number->isValidNumber()) {
      // strict check relying on up-to-date metadata library
      return "";
    }

    return $number->format(PhoneNumberFormat::E164);
  }

  public function toE164()
  {
    return $this->number->format(PhoneNumberFormat::E164);
  }

  public function toInternational()
  {
    return $this->number->format(PhoneNumberFormat::INTERNATIONAL);
  }

  public function toNational()
  {
    return $this->number->format(PhoneNumberFormat::NATIONAL);
  }

  public function toRfc()
  {
    return $this->number->format(PhoneNumberFormat::RFC3966);
  }

  public function forCallingFrom($isoCode = "GB")
  {
    return $this->number->formatForCallingFrom($isoCode);
  }

  public static function validationRules()
  {
    return [
      'phone' => new ValidPhone,
    ];
  }
}