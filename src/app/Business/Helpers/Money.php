<?php

namespace App\Business\Helpers;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money as PHPMoney;
use NumberFormatter;

class Money
{
    public static function formatCurrency($amount, $currency = 'GBP')
    {
        $money = new PHPMoney($amount, new Currency($currency));
        $currencies = new ISOCurrencies();

        $numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($money);
    }

    public static function formatDecimal($amount, $currency = 'GBP')
    {
        $money = new PHPMoney($amount, new Currency($currency));
        $currencies = new ISOCurrencies();

        $numberFormatter = new \NumberFormatter('en_GB', \NumberFormatter::DECIMAL);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($money);
    }
}
