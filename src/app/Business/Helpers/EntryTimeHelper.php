<?php

namespace App\Business\Helpers;

use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Illuminate\Support\Str;

class EntryTimeHelper
{
    public static function toDecimal(?string $time): ?string
    {
        $output = null;
        if ($time) {
            $arr = explode(':', $time);
            if (count($arr) == 2) {
                $output = ((BigDecimal::of($arr[0]))->multipliedBy('60'))->plus(BigDecimal::of($arr[1]));
            } elseif (count($arr) == 1) {
                $output = BigDecimal::of($arr[0]);
            }
        }

        $ret = (string) $output;
        if ($output == null || Str::length($ret) == 0) {
            $ret = null;
        }

        return $ret;
    }

    public static function formatted(null|string|float $time): string
    {
        $output = 'No time';
        if ($time) {
            $time = BigDecimal::of($time);
            $minutes = BigInteger::of($time->getIntegralPart())->quotient('60');
            $secs = BigInteger::of($time->getIntegralPart())->mod('60');
            $hundreds = $time->getFractionalPart();

            return Str::padLeft($minutes, 2, '0').':'.Str::padLeft($secs, 2, '0').'.'.Str::padLeft($hundreds, 2, '0');
        }

        return $output;
    }
}
