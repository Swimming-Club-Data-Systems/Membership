<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Enums\StripePaymentIntentStatus;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\Gala;
use App\Models\Tenant\GalaEntry;
use App\Models\Tenant\Member;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use App\Models\Tenant\PaymentMethod;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $paymentIntent = $stripe->paymentIntents->retrieve($payment->stripe_id, [], [
            'stripe_account' => $tenant->stripeAccount(),
        ]);

//        $paymentIntent = $stripe->paymentIntents->create([
//            'currency' => 'gbp',
//            'amount' => 999,
//            'automatic_payment_methods' => [
//                'enabled' => true,
//            ],
//            'customer' => $user?->stripeCustomerId(),
//        ], [
//            'stripe_account' => $tenant->stripeAccount(),
//        ]);

        return Inertia::render('Payments/Checkout/Checkout', [
            'id' => $payment->id,
            'stripe_publishable_key' => config('services.stripe.key'),
            'client_secret' => $paymentIntent->client_secret,
            'stripe_account' => $tenant->stripeAccount(),
            'payment_methods' => $paymentMethodsArray,
            'country' => 'GB',
            'currency' => $paymentIntent->currency,
            'total' => $paymentIntent->amount,
            'formatted_total' => Money::formatCurrency($paymentIntent->amount),
            'return_url' => route("payments.checkout.show", $payment),
            'lines' => $lines,
        ]);
    }

    public function create(): \Symfony\Component\HttpFoundation\Response|null
    {
        DB::beginTransaction();
        try {
            /** @var User $user */
            $user = Auth::user();

            /** @var Tenant $tenant */
            $tenant = tenant();

            $gala = new Gala();
            $gala->GalaName = "Chester-le-Street Open Meet 2023";
            $gala->CourseLength = "SHORT";
            $gala->GalaVenue = "Chester-le-Street Leisure Centre";
            $gala->GalaFee = 8.00;
            $gala->GalaFeeConstant = true;
            $gala->ClosingDate = Carbon::today();
            $gala->GalaDate = Carbon::today();
            $gala->HyTek = true;
            $gala->CoachEnters = false;
            $gala->RequiresApproval = false;
            $gala->Description = "";
            $gala->ProcessingFee = 50;
            $gala->save();

            $galaEntry = new GalaEntry();
            $galaEntry->member()->associate(Member::find(17));
//            $galaEntry->gala = $gala;
            $galaEntry->setAttribute('50Free', true);
            $galaEntry->setAttribute('50Back', true);
            $galaEntry->setAttribute('50Breast', true);
            $galaEntry->setAttribute('50Fly', true);
            $galaEntry->FeeToPay = 32.50;

            $gala->entries()->save($galaEntry);

            $payment = new Payment();
            $payment->user()->associate($user);
            // $payment->paymentMethod()->associate($user);
            // $payment->application_fee_amount = ApplicationFeeAmount::calculateAmount($this->topUp->amount);
            $payment->save();

            $lineItem = new PaymentLine();
            $lineItem->unit_amount = $galaEntry->amount;
            $lineItem->quantity = 1;
            $lineItem->currency = 'gbp';
            $lineItem->associated()->associate($galaEntry);
            $payment->lines()->save($lineItem);

            $payment->refresh();

            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $paymentIntent = $stripe->paymentIntents->create([
                'currency' => 'gbp',
                'amount' => $payment->amount,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'customer' => $payment->user?->stripeCustomerId(),
            ], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);
            $payment->stripe_id = $paymentIntent->id;
            $payment->save();

            DB::commit();
            return redirect(route("payments.checkout.show", $payment));
        } catch (\Exception $e) {
            ddd($e);
        }

        return null;
    }

    public function success(Payment $payment): \Inertia\Response|\Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('pay', $payment);

//        if ($payment->payable()) {
//            abort(404, 'This payment is not complete.');
//        }

        /** @var Tenant $tenant */
        $tenant = tenant();

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
        $paymentIntent = $stripe->paymentIntents->retrieve($payment->stripe_id, [], [
            'stripe_account' => $tenant->stripeAccount(),
        ]);

        if ($paymentIntent->status === StripePaymentIntentStatus::SUCCEEDED->value) {
            return Inertia::render('Payments/Checkout/Success', [
                'id' => $payment->id,
            ]);
        } else if ($paymentIntent->status === StripePaymentIntentStatus::PROCESSING->value) {
            return Inertia::render('Payments/Checkout/InProgress', [
                'id' => $payment->id,
            ]);
        } else {
            return Inertia::location(route("payments.checkout.show", $payment));
        }
    }
}
