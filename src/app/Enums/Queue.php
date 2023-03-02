<?php

namespace App\Enums;

enum Queue: string
{
    case DEFAULT = 'default';
    case NOTIFY = 'notify';
    case STRIPE = 'stripe';
}
