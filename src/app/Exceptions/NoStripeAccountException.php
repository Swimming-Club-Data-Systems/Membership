<?php

namespace App\Exceptions;

use Exception;

class NoStripeAccountException extends Exception
{
    public $message = 'This tenant has not connected a Stripe account.';
}
