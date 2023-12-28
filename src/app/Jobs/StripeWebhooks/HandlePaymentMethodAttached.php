<?php

namespace App\Jobs\StripeWebhooks;

use App\Enums\Queue;
use App\Models\Central\Tenant;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\StripeCustomer;
use App\Traits\JobBackoff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class HandlePaymentMethodAttached implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, JobBackoff, Queueable, SerializesModels;

    public WebhookCall $webhookCall;

    public int $webhookCallId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $webhookCallId)
    {
        $this->webhookCallId = $webhookCallId;
        // $this->onQueue(Queue::STRIPE->value);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->webhookCall = WebhookCall::findOrFail($this->webhookCallId);

        // Find if there is a user for this customer in the system
        // Check if PM is in the database already
        // If not add the payment method to the database

        /** @var Tenant $tenant */
        $tenant = Tenant::findByStripeAccountId($this->webhookCall->payload['account']);

        $tenant->run(function () {
            try {
                \Stripe\Stripe::setApiKey(config('cashier.secret'));

                // Get a user if they exist
                /** @var StripeCustomer $customer */
                $customer = StripeCustomer::firstWhere('CustomerID', '=', $this->webhookCall->payload['data']['object']['customer']);

                if (! $customer) {
                    // Stop executing
                    return;
                }

                // See if it's already in the database
                $paymentMethod = PaymentMethod::firstWhere('stripe_id', '=', $this->webhookCall->payload['data']['object']['id']);

                if (! $paymentMethod) {
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
                    $paymentMethod->fingerprint = $paymentMethod->pm_type_data?->fingerprint;
                    $paymentMethod->user()->associate($customer->user);
                    $paymentMethod->created_at = $pm->created;

                    $paymentMethod->save();
                }
            } catch (QueryException) {
                // Will be not unique, ignore this case
            }
        });
    }
}
