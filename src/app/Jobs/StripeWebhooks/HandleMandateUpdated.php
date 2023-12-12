<?php

namespace App\Jobs\StripeWebhooks;

use App\Enums\Queue;
use App\Mail\Payments\MandateInactive;
use App\Models\Central\Tenant;
use App\Models\Tenant\Mandate;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\StripeCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleMandateUpdated implements ShouldQueue
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
        $this->onQueue(Queue::STRIPE->value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->webhookCall->payload['livemode'] != config('stripe.livemode')) {
            return;
        }

        /** @var Tenant $tenant */
        $tenant = Tenant::findByStripeAccountId($this->webhookCall->payload['account']);

        $tenant->run(function () {
            try {
                \Stripe\Stripe::setApiKey(config('cashier.secret'));

                // Get a user if they exist
                /** @var StripeCustomer $customer */
                $mandate = Mandate::firstWhere('stripe_id', '=', $this->webhookCall->payload['data']['object']['id']);

                $stripeMandate = \Stripe\Mandate::retrieve([
                    'id' => $this->webhookCall->payload['data']['object']['id'],
                ], [
                    'stripe_account' => $this->webhookCall->payload['account'],
                ]);

                /** @var PaymentMethod $paymentMethod */
                $paymentMethod = PaymentMethod::firstWhere('stripe_id', '=', $stripeMandate->payment_method);

                if ($mandate) {
                    // Update data
                    if ($paymentMethod) {
                        // Ensure it is associated with the PM
                        $mandate->paymentMethod()->associate($paymentMethod);
                    }
                    $mandate->stripe_id = $stripeMandate->id;
                    $mandate->type = $stripeMandate->type;
                    $mandate->customer_acceptance = $stripeMandate->customer_acceptance;
                    $type = $stripeMandate->payment_method_details->type;
                    $mandate->pm_type_details = $stripeMandate->payment_method_details->$type;
                    $mandate->status = $stripeMandate->status;
                    $mandate->save();
                }

                $user = null;
                $newDefault = null;

                if ($paymentMethod?->user && $stripeMandate->status == 'inactive') {
                    // The associated payment method may no longer be used for payments.

                    // Get the user
                    $user = $paymentMethod->user;

                    // Dissociate the PaymentMethod from any users
                    $paymentMethod->user()->dissociate();
                    $paymentMethod->save();

                    // Find a new default bacs_debit for the user
                    /** @var PaymentMethod $newDefault */
                    $newDefault = $user->paymentMethods()->where('type', '=', 'bacs_debit')->first();
                    if ($newDefault) {
                        $newDefault->default = true;
                        $newDefault->save();
                    }
                }

                // Now send the user an email stating the DDI has been cancelled.
                // If a new default has been set, include details of it and its mandate in the email
                if ($user && $mandate) {
                    Mail::to($user)->send(new MandateInactive($user, $mandate, $paymentMethod, $newDefault));
                }

            } catch (\Exception) {

            }
        });
    }
}
