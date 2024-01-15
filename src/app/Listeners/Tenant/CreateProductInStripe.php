<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\ProductCreating;
use App\Models\Central\Tenant;

class CreateProductInStripe
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
    public function handle(ProductCreating $event): void
    {
        if (! $event->product->stripe_id) {
            /** @var Tenant $tenant */
            $tenant = tenant();
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            $stripeProduct = $stripe->products->create([
                [
                    'name' => $event->product->name,
                    'description' => $event->product->description,
                    'shippable' => $event->product->shippable,
                    'unit_label' => $event->product->unit_label,
                ],
            ], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            $event->product->stripe_id = $stripeProduct->id;
        }
    }
}
