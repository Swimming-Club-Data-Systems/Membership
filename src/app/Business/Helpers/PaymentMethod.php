<?php

namespace App\Business\Helpers;

use Illuminate\Support\Facades\Date;

class PaymentMethod
{
    public static function formatName($paymentMethod)
    {
        switch ($paymentMethod->type) {
            case 'card':
                return self::formatCardBrandName($paymentMethod->card->brand) . ' ···· ' . $paymentMethod->card->last4;
            case 'bacs_debit':
                return 'Bacs Direct Debit ···· ' . $paymentMethod->bacs_debit->last4;
            default:
                return $paymentMethod->type . ': ' . $paymentMethod->id;
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
        switch ($paymentMethod->type) {
            case 'card':
                $expiry = Date::now();
                $expiry->day = 1;
                $expiry->year = $paymentMethod->card->exp_year;
                $expiry->month = $paymentMethod->card->exp_month;
                return 'Expires ' . $expiry->monthName . ' ' . $expiry->year;
            case 'bacs_debit':
                return implode("-", str_split($paymentMethod->bacs_debit->sort_code, 2));
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
