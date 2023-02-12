<?php

namespace App\Business\Helpers;

use App\Models\Central\Tenant;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class ApplicationFeeAmount
{
    /**
     * Calculate the application fee for a tenant
     *
     * @param $amount
     * @param Tenant|null $tenant
     * @return integer
     */
    public static function calculateAmount($amount, Tenant $tenant = null): int
    {
        // Return the min of app fee or amount
        return min($amount, self::calculate($amount, $tenant));
    }

    /**
     * @param $amount
     * @param Tenant|null $tenant
     * @return integer
     */
    protected static function calculate($amount, Tenant $tenant = null): int
    {
        if (!$tenant) {
            $tenant = tenant();
        }
        /** @var Tenant $tenant */

        $applicationFeeType = $tenant->application_fee_type;
        $applicationFeeAmount = $tenant->application_fee_amount;

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
