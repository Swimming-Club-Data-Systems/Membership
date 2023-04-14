<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $description
 * @property int $amount
 * @property string $formatted_amount
 */
class RefundPaymentLine extends Pivot
{

    protected $table = 'refund_v2_payment_line';

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => Money::formatCurrency($this->amount, 'GBP'),
        );
    }
}
