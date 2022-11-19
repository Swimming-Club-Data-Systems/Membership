<?php

namespace App\Jobs\StripeWebhooks;

use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\StripeCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class HandlePaymentMethodAttached implements ShouldQueue
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
        // Find if there is a user for this customer in the system
        // Check if PM is in the database already
        // If not add the payment method to the database

        // Get a user if they exist
        /** @var StripeCustomer $customer */
        $customer = StripeCustomer::firstWhere('CustomerID', '=', $this->webhookCall->payload['data']['object']['customer']);

        if (!$customer) {
            // Stop executing
            return;
        }

        // See if it's already in the database
        $paymentMethod = PaymentMethod::firstWhere('stripe_id', '=', $this->webhookCall->payload['data']['object']['id']);

        if (!$paymentMethod) {
            $pm = \Stripe\PaymentMethod::retrieve([
                'id' => $this->webhookCall->payload['data']['object']['id'],
                'expand' => ['billing_details.address'],
            ], [
                'stripe_account' => $this->webhookCall->payload['account'],
            ]);

            $paymentMethod = new PaymentMethod();
            $paymentMethod->stripe_id = $pm->id;
            $type = $pm->type;
            $paymentMethod->type = $type;
            $paymentMethod->pm_type_data = $pm->$type;
            $paymentMethod->billing_address = $pm->billing_details;
            $paymentMethod->user()->associate($customer->user);
            $paymentMethod->created_at = $pm->created;

            $paymentMethod->save();
        }
    }
}
