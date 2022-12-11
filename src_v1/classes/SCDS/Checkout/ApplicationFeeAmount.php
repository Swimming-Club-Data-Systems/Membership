<?php

namespace SCDS\Checkout;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class ApplicationFeeAmount
{
    /**
     * @param $amount
     * @return integer
     */
    public static function calculateAmount($amount)
    {
        // Return the min of app fee or amount
        return min($amount, self::calculate($amount));
    }

    protected static function calculate($amount)
    {
        /** @var \Tenant $tenant */
        $tenant = app()->tenant;

        $applicationFeeType = $tenant->getJsonData()->application_fee_type;
        $applicationFeeAmount = $tenant->getJsonData()->application_fee_amount;

        if (!$applicationFeeType || (int)$applicationFeeAmount === 0) {
            return 0;
        }

        if ($applicationFeeType === "fixed") {
            return (int)$applicationFeeAmount;
        } else if ($applicationFeeType === "percent") {
            $percent = BigDecimal::of($applicationFeeAmount)->withPointMovedLeft(2);
            $multiplier = $percent->dividedBy('100', 4, RoundingMode::HALF_UP);
            return BigDecimal::of($amount)->multipliedBy($multiplier)->toScale(0, RoundingMode::HALF_UP)->toInt();
        } else {
            return 0;
        }
    }
}