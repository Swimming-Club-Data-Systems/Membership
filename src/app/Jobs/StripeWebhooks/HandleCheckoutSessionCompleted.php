<?php

namespace App\Jobs\StripeWebhooks;

use App\Models\Central\Tenant;
use App\Models\Tenant\Mandate;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\StripeCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
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

        /** @var Tenant $tenant */
        $tenant = Tenant::findByStripeAccountId($this->webhookCall->payload['account']);

        $tenant->run(function () {
            try {
                \Stripe\Stripe::setApiKey(config('cashier.secret'));

                $checkoutSession = \Stripe\Checkout\Session::retrieve($this->webhookCall->payload['data']['object']['id'], [
                    'expand' => ['payment_method', 'payment_method.billing_details.address', 'mandate'],
                    'stripe_account' => $this->webhookCall->payload['account'],
                ]);

                if ($checkoutSession->setup_intent) {
                    $setupIntent = \Stripe\SetupIntent::retrieve([
                        'id' => $checkoutSession->setup_intent,
                        'expand' => ['payment_method', 'payment_method.billing_details.address', 'mandate'],
                    ], [
                        'stripe_account' => $this->webhookCall->payload['account'],
                    ]);

                    // See if it's already in the database
                    $paymentMethod = PaymentMethod::firstWhere('stripe_id', '=', $setupIntent->payment_method->id);

                    if (!$paymentMethod) {
                        // Add to the database

                        $paymentMethod = new PaymentMethod();
                        $paymentMethod->stripe_id = $setupIntent->payment_method->id;
                        $type = $setupIntent->payment_method->type;
                        $paymentMethod->type = $type;
                        $paymentMethod->pm_type_data = $setupIntent->payment_method->$type;
                        $paymentMethod->billing_address = $setupIntent->payment_method->billing_details;

                        if ($setupIntent->customer) {
                            /** @var StripeCustomer $customer */
                            $customer = StripeCustomer::firstWhere('CustomerID', $setupIntent->customer);
                            if ($customer) {
                                $paymentMethod->user()->associate($customer->user);
                            }
                        }

                        $paymentMethod->created_at = $setupIntent->payment_method->created;

                        $paymentMethod->save();
                    }

                    if ($setupIntent->mandate) {
                        $mandate = Mandate::firstWhere('stripe_id', '=', $setupIntent->mandate->id);

                        if (!$mandate) {
                            $mandate = new Mandate();
                            $mandate->paymentMethod()->associate($paymentMethod);
                            $mandate->stripe_id = $setupIntent->mandate->id;
                            $mandate->type = $setupIntent->mandate->type;
                            $mandate->customer_acceptance = $setupIntent->mandate->customer_acceptance;
                            $type = $setupIntent->mandate->payment_method_details->type;
                            $mandate->pm_type_details = $setupIntent->mandate->payment_method_details->$type;
                            $mandate->status = $setupIntent->mandate->status;
                            $mandate->save();
                        }
                    }

                }
            } catch (QueryException) {
                // Will be not unique, ignore this case
            } catch (\Throwable $e) {
                report($e);
                throw $e;
            }
        });
    }
}
