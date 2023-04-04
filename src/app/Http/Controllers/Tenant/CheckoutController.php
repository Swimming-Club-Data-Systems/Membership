<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\CustomerStatement;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function show(Payment $payment): \Inertia\Response
    {
        $this->authorize('pay', $payment);

        if (!$payment->payable()) {
            abort(404, 'A Payment Method is not required at this time.');
        }

        /** @var User $user */
        $user = Auth::user();

        /** @var Tenant $tenant */
        $tenant = tenant();

        $paymentMethods = $user?->paymentMethods()
        ->where('type', 'card')
        ->orderBy('created_at', 'asc')
        ->get();
        $paymentMethodsArray = [];
        foreach ($paymentMethods as $method) {
            /** @var PaymentMethod $method */
            $paymentMethodsArray[] = [
                'id' => $method->id,
                'stripe_id' => $method->stripe_id,
                'description' => $method->description,
                'information_line' => $method->information_line,
            ];
        }

        $lines = [];
        foreach ($payment->lines()->get() as $line) {
            /** @var PaymentLine $line */
            $lines[] = [
                'id' => $line->id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'formatted_amount' => $line->formatted_amount_total,
            ];
        }

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        $paymentIntent = $stripe->paymentIntents->create([
            'currency' => 'gbp',
            'amount' => 999,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'customer' => $user?->stripeCustomerId(),
        ], [
            'stripe_account' => $tenant->stripeAccount(),
        ]);

        return Inertia::render('Payments/Checkout/Checkout', [
            'id' => $payment->id,
            'stripe_publishable_key' => config('services.stripe.key'),
            'client_secret' => $paymentIntent->client_secret,
            'stripe_account' => $tenant->stripeAccount(),
            'payment_methods' => $paymentMethodsArray,
            'country' => 'GB',
            'currency' => $paymentIntent->currency,
            'total' => $paymentIntent->amount,
            'return_url' => route("payments.checkout.show", $payment),
            'lines' => $lines,
        ]);
    }
}
