<?php

namespace App\Enums;

enum StripePriceTaxBehavior: string
{
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';
    case UNSPECIFIED = 'unspecified';
}
