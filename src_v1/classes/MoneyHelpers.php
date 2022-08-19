<?php

class MoneyHelpers
{
  public static function intToDecimal($amount)
  {
    return (string) (\Brick\Math\BigDecimal::of((string) $amount))->withPointMovedLeft(2)->toScale(2);
  }

  public static function decimalToInt($amount)
  {
    return \Brick\Math\BigDecimal::of((string) $amount)->withPointMovedRight(2)->toInt();
  }

  public static function formatCurrency($amount, $currency)
  {
    $formatter = new \NumberFormatter(app()->locale, \NumberFormatter::CURRENCY);
    return $formatter->formatCurrency((string) $amount, $currency);
  }
}