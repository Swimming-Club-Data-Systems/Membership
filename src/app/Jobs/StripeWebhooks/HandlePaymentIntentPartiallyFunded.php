<?php

namespace App\Jobs\StripeWebhooks;

use App\Enums\Queue;
use App\Traits\JobBackoff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

/**
 * Job required to handle the case where a Stripe Customer Balance is partially, but not fully funded
 *
 * Need to tell the customer that they have more to pay
 */
class HandlePaymentIntentPartiallyFunded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, JobBackoff, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public WebhookCall $webhookCall
    ) {
        $this->onQueue(Queue::STRIPE->value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
