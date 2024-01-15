<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\PriceCreating;
use App\Models\Central\Tenant;

class CreatePriceInStripe
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PriceCreating $event): void
    {
        if (! $event->price->stripe_id) {
            /** @var Tenant $tenant */
            $tenant = tenant();
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            $stripePrice = $stripe->prices->create([
                [
                    'currency' => $event->price->currency,
                    'nickname' => $event->price->nickname,
                    'product' => $event->price->product->stripe_id,
                    'unit_amount' => $event->price->unit_amount,
                    'billing_scheme' => $event->price->billing_scheme->value,
                    'tax_behavior' => $event->price->tax_behavior->value,
                ],
            ], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            $event->price->stripe_id = $stripePrice->id;
        }
    }
}
