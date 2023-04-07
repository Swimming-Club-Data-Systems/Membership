<?php

namespace App\Business\Helpers;

use Illuminate\Support\Facades\Date;

class PaymentMethod
{
    public static function formatName($paymentMethod)
    {
        $type = $paymentMethod->type;
        return self::formatNameFromData($type, $paymentMethod->$type);
    }

    public static function formatNameFromData($type, $typeData)
    {
        $typeData = json_decode(json_encode($typeData));
        switch ($type) {
            case 'card':
                return self::formatCardBrandName($typeData->brand) . ' ···· ' . $typeData->last4;
            case 'bacs_debit':
                return 'Bacs Direct Debit ···· ' . $typeData->last4;
            default:
                return $type;
        }
    }

    public static function formatCardBrandName(string $brand): string
    {
        switch ($brand) {
            case 'amex':
                return 'American Express';
            case 'diners':
                return 'Diners Club';
            case 'discover':
                return 'Discover';
            case 'jcb':
                return 'JCB';
            case 'mastercard':
                return 'Mastercard';
            case 'unionpay':
                return 'UnionPay';
            case 'visa':
                return 'Visa';
            default:
                break;
        }
        return "Unknown";
    }

    public static function formatInfoLine($paymentMethod)
    {
        $type = $paymentMethod->type;
        return self::formatInfoLineFromData($type, $paymentMethod->$type);
    }

    public static function formatInfoLineFromData($type, $typeData)
    {
        $typeData = json_decode(json_encode($typeData));
        switch ($type) {
            case 'card':
                $expiry = Date::now();
                $expiry->day = 1;
                $expiry->year = $typeData->exp_year;
                $expiry->month = $typeData->exp_month;
                return 'Expires ' . $expiry->monthName . ' ' . $expiry->year;
            case 'bacs_debit':
                return implode("-", str_split($typeData->sort_code, 2));
            default:
                return null;
        }
    }

    public static function formatCardFundingType(string $funding): string
    {
        switch ($funding) {
            case 'credit':
                return 'Credit';
            case 'debit':
                return 'Debit';
        }
        return "Prepaid";
    }
}
