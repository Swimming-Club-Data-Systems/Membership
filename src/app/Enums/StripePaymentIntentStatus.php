<?php

namespace App\Enums;

enum StripePaymentIntentStatus: string
{
    case REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
    case REQUIRES_CONFIRMATION = 'requires_confirmation';
    case REQUIRES_ACTION = 'requires_action';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case CANCELED = 'canceled';
}
