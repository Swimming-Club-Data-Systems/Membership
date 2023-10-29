<?php

namespace App\Listeners;

use App\Business\Helpers\PaymentMethod;
use App\Models\Central\Tenant;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    /**
     * Handle received Stripe webhooks.
     *
     * @return void
     */
    public function handle(WebhookReceived $event)
    {
        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        if ($event->payload['type'] === 'checkout.session.completed') {
            // Handle the incoming event...
            if ($event->payload['data']['object']['payment_status'] == 'paid' && $event->payload['data']['object']['metadata']['type'] == 'tenant_account_top_up') {
                try {
                    /** @var Tenant $tenant */
                    $tenant = Tenant::find($event->payload['data']['object']['metadata']['tenant']);

                    $paymentIntent = $stripe->paymentIntents->retrieve($event->payload['data']['object']['payment_intent'], [
                        'expand' => ['payment_method'],
                    ]);

                    $transaction = $tenant->journal->credit($paymentIntent->amount, 'Account Top Up ('.PaymentMethod::formatName($paymentIntent->payment_method).') (PI '.$paymentIntent->id.')');

                } catch (\Exception $e) {

                }
            }
        }
    }
}
