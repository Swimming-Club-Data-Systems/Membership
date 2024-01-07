<?php

namespace App\Enums;

enum StripePriceType: string
{
    case ONE_TIME = 'one_time';
    case RECURRING = 'recurring';
}
