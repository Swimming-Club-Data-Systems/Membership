<?php

namespace App\Enums;

enum StripePriceBillingScheme: string
{
    case PER_UNIT = 'per_unit';
    case TIERED = 'tiered';
}
