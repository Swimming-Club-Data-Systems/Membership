<?php

namespace App\Jobs\StripeWebhooks;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleCheckoutSessionCompleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // do your work here

        // you can access the payload of the webhook call with `$this->webhookCall->payload`

        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        $checkoutSession = \Stripe\Checkout\Session::retrieve($this->webhookCall->payload['data']['object']['id'], [
            'expand' => ['payment_method', 'payment_method.billing_details.address', 'mandate'],
            'stripe_account' => $this->webhookCall->payload['account'],
        ]);

        if ($checkoutSession->setup_intent)
        {
            $setupIntent = \Stripe\SetupIntent::retrieve([
                'id' => $checkoutSession->setup_intent,
                'expand' => ['payment_method', 'payment_method.billing_details.address', 'mandate'],
            ], [
                'stripe_account' => $this->webhookCall->payload['account'],
            ]);

            // Add to the database


        }
    }
}
