<?php

namespace App\Business\Helpers;

use App\Rules\ValidPhone;
use Brick\PhoneNumber\PhoneNumber as BrickPhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseException;

/**
 * Formats and deals with phone numbers
 */
class PhoneNumber
{
    protected BrickPhoneNumber $number;

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

    public static function toDatabaseFormat(string $number, string $country = 'GB')
    {
        try {
            $number = BrickPhoneNumber::parse($number, $country);
        } catch (PhoneNumberParseException $e) {
            // 'The string supplied is too short to be a phone number.'
            return '';
        }

        if (! $number->isValidNumber()) {
            // strict check relying on up-to-date metadata library
            return '';
        }

        return $number->format(PhoneNumberFormat::E164);
    }

    public static function validationRules()
    {
        return [
            'phone' => new ValidPhone,
        ];
    }

    public function toE164(): string
    {
        return $this->number->format(PhoneNumberFormat::E164);
    }

    public function toInternational(): string
    {
        return $this->number->format(PhoneNumberFormat::INTERNATIONAL);
    }

    public function toNational(): string
    {
        return $this->number->format(PhoneNumberFormat::NATIONAL);
    }

    public function toRfc(): string
    {
        return $this->number->format(PhoneNumberFormat::RFC3966);
    }

    public function forCallingFrom($isoCode = 'GB'): string
    {
        return $this->number->formatForCallingFrom($isoCode);
    }

    public function getDescription(): ?string
    {
        return $this->number->getDescription('en_GB', 'GB');
    }
}
