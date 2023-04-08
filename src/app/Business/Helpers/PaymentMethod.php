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
            case 'klarna':
                return 'Klarna';
            case 'acss_debit':
                return 'ACSS ' . $typeData->bank_name . ' ···· ' . $typeData->last4;
            case 'affirm':
                return 'Affirm';
            case 'afterpay_clearpay':
                return 'Clearpay';
            case 'alipay':
                return 'Alipay';
            case 'au_becs_debit':
                return 'BECS Direct Debit ···· ' . $typeData->last4;
            case 'bancontact':
                return 'Bancontact';
            case 'blik':
                return 'BLIK';
            case 'boleto':
                return 'Boleto';
            case 'cashapp':
                return 'Cash App';
            case 'customer_balance':
                return 'Customer Balance (S)';
            case 'eps':
                return 'EPS';
            case 'fpx':
                return 'fpx';
            case 'giropay':
                return 'Giropay';
            case 'grabpay':
                return 'GrabPay';
            case 'ideal':
                return 'iDEAL';
            case 'interac_present':
                return 'Interac';
            case 'konbini':
                return 'Konbini';
            case 'link':
                return 'Link';
            case 'oxxo':
                return 'OXXO';
            case 'p24':
                return 'P24';
            case 'paynow':
                return 'PayNow';
            case 'pix':
                return 'Pix';
            case 'promptpay':
                return 'PromptPay';
            case 'sepa_debit':
                return 'SEPA Direct Debit ···· ' . $typeData->last4;
            case 'sofort':
                return 'SOFORT';
            case 'us_bank_account':
                return 'U.S. Bank Account ···· ' . $typeData->last4;
            case 'wechat_pay':
                return 'WeChat Pay';
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
