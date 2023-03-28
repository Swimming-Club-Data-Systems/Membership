<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\Payment;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function show(Payment $payment): \Inertia\Response
    {
        $this->authorize('pay', $payment);

        if (!$payment->payable()) {
            abort(404, 'A Payment Method is not required at this time.');
        }

        /** @var Tenant $tenant */
        $tenant = tenant();

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $paymentIntent = $stripe->paymentIntents->create([
            'currency' => 'gbp',
            'amount' => 999,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ], [
            'stripe_account' => $tenant->stripeAccount(),
        ]);

        return Inertia::render('Payments/Checkout/Checkout', [
            'id' => $payment->id,
            'stripe_publishable_key' => config('services.stripe.key'),
            'client_secret' => $paymentIntent->client_secret,
            'stripe_account' => $tenant->stripeAccount(),
        ]);
    }
}
