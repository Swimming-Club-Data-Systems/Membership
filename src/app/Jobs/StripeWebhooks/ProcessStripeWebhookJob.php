<?php

namespace App\Jobs\StripeWebhooks;

class ProcessStripeWebhookJob extends \Spatie\StripeWebhooks\ProcessStripeWebhookJob
{
    /**
     * @var int
     *
     * Delay execution to allow for the DB to catch up
     */
    public $delay = 5;
}
