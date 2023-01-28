<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\Mandate;
use App\Models\Tenant\StripeCustomer;
use App\Models\Tenant\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $directDebits = $user->paymentMethods()->where('type', '=', 'bacs_debit')->get();
        $otherMethods = $user->paymentMethods()->where('type', '!=', 'bacs_debit')->get();

        $default = null;

        $map = function (\App\Models\Tenant\PaymentMethod $item) use ($default) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'description' => PaymentMethod::formatNameFromData($item->type, $item->pm_type_data),
                'created' => $item->created_at,
                'info_line' => PaymentMethod::formatInfoLineFromData($item->type, $item->pm_type_data),
                'default' => (bool)$item->default,
            ];
        };

        /** @var \App\Models\Tenant\PaymentMethod $defaultPm */
        $defaultPm = $user->paymentMethods()->firstWhere('default', '=', true);
        if ($defaultPm) {
            $default = $map($defaultPm);
        }

        return Inertia::render('Payments/PaymentMethods', [
            'direct_debits' => $directDebits->map($map),
            'payment_methods' => $otherMethods->map($map),
            'payment_method' => $default,
        ]);
    }

    public function addPaymentMethod()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        /** @var User $user */
        $user = Auth::user();

        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        abort_unless($tenant->getOption('STRIPE_ACCOUNT_ID'), 404);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'setup',
            'customer' => $user->stripeCustomerId(),
            'success_url' => route('payments.methods.new_success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payments.methods.index'),
            'locale' => 'en-GB',
            'metadata' => [
                'session_type' => 'direct_debit_setup',
            ],
        ], [
            'stripe_account' => $tenant->stripeAccount()
        ]);

        return Inertia::location($session->url);
    }

    public function addDirectDebit()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        /** @var User $user */
        $user = Auth::user();

        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        abort_unless($tenant->getOption('STRIPE_ACCOUNT_ID'), 404);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['bacs_debit'],
            'mode' => 'setup',
            'customer' => $user->stripeCustomerId(),
            'success_url' => route('payments.methods.new_success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payments.methods.index'),
            'locale' => 'en-GB',
            'metadata' => [
                'session_type' => 'direct_debit_setup',
            ],
        ], [
            'stripe_account' => $tenant->stripeAccount()
        ]);

        return Inertia::location($session->url);
    }

    public function addPaymentMethodSuccess(Request $request)
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        /** @var Tenant $tenant */
        $tenant = tenant();

        if ($request->input('session_id')) {

            try {
                // Get the checkout session
                $checkoutSession = \Stripe\Checkout\Session::retrieve([
                    'id' => $request->input('session_id'),
                    'expand' => ['setup_intent', 'setup_intent.payment_method', 'setup_intent.payment_method.billing_details.address', 'setup_intent.mandate'],
                ], [
                    'stripe_account' => $tenant->stripeAccount(),
                ]);

                $flashBag = $checkoutSession->setup_intent->payment_method->type == "bacs_debit" ? 'direct_debit' : 'payment_method';

                // Get a user if they exist
                /** @var StripeCustomer $customer */
                $customer = StripeCustomer::firstWhere('CustomerID', '=', $checkoutSession->customer);

                if (!$customer) {
                    // Stop executing
                    throw new \Exception('No user was found');
                }

                // See if it's already in the database
                $paymentMethod = \App\Models\Tenant\PaymentMethod::firstWhere('stripe_id', '=', $checkoutSession->setup_intent->payment_method->id);

                if (!$paymentMethod) {
                    // Add to the database

                    $paymentMethod = new \App\Models\Tenant\PaymentMethod();
                    $paymentMethod->stripe_id = $checkoutSession->setup_intent->payment_method->id;
                    $type = $checkoutSession->setup_intent->payment_method->type;
                    $paymentMethod->type = $type;
                    $paymentMethod->pm_type_data = $checkoutSession->setup_intent->payment_method->$type;
                    $paymentMethod->billing_address = $checkoutSession->setup_intent->payment_method->billing_details;

                    if ($checkoutSession->setup_intent->customer) {
                        /** @var StripeCustomer $customer */
                        $customer = StripeCustomer::firstWhere('CustomerID', $checkoutSession->setup_intent->customer);
                        if ($customer) {
                            $paymentMethod->user()->associate($customer->user);
                        }
                    }

                    $paymentMethod->created_at = $checkoutSession->setup_intent->payment_method->created;

                    $paymentMethod->save();
                }

                if ($checkoutSession->setup_intent->mandate) {
                    $mandate = Mandate::firstWhere('stripe_id', '=', $checkoutSession->setup_intent->mandate->id);

                    if (!$mandate) {
                        $mandate = new Mandate();
                        $mandate->paymentMethod()->associate($paymentMethod);
                        $mandate->stripe_id = $checkoutSession->setup_intent->mandate->id;
                        $mandate->type = $checkoutSession->setup_intent->mandate->type;
                        $mandate->customer_acceptance = $checkoutSession->setup_intent->mandate->customer_acceptance;
                        $type = $checkoutSession->setup_intent->mandate->payment_method_details->type;
                        $mandate->pm_type_details = $checkoutSession->setup_intent->mandate->payment_method_details->$type;
                        $mandate->status = $checkoutSession->setup_intent->mandate->status;
                        $mandate->save();
                    }
                }
            } catch (QueryException) {
                // Will be not unique, ignore this case
            } catch (\Throwable $e) {
                report($e);
                throw $e;
            }

            $request->session()->flash('flash_bag.' . $flashBag . '.success', 'We have saved ' . PaymentMethod::formatName($checkoutSession->setup_intent->payment_method) . ' to your list of payment methods.');
        }

        return Inertia::location(route('payments.methods.index'));
    }

    public
    function update(\App\Models\Tenant\PaymentMethod $paymentMethod, Request $request)
    {
        $type = $paymentMethod->type == "bacs_debit" ? 'direct_debit' : 'payment_method';

        try {
            if ($request->input('set_default')) {
                $paymentMethod->default = true;
                $paymentMethod->save();

                $request->session()->flash('flash_bag.' . $type . '.success', 'We have set ' . PaymentMethod::formatNameFromData($paymentMethod->type, $paymentMethod->pm_type_data) . ' as your default Direct Debit.');
            }
        } catch (\Exception $e) {
            $request->session()->flash('flash_bag.' . $type . '.error', $e->getMessage());
        }

        return Redirect::route('payments.methods.index');
    }

    /**
     * @throws ValidationException
     */
    public
    function delete(\App\Models\Tenant\PaymentMethod $paymentMethod, Request $request)
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $type = $paymentMethod->type == "bacs_debit" ? 'direct_debit' : 'payment_method';

        if ($paymentMethod->default) {
            $request->session()->flash('flash_bag.' . $type . '.error', 'You can not delete a default payment method');
            return Redirect::route('payments.methods.index');
        }

        try {
            // Detach customer in Stripe so this PM can not be used again
            $stripe = new \Stripe\StripeClient(
                config('cashier.secret')
            );
            $pm = $stripe->paymentMethods->detach(
                $paymentMethod->stripe_id,
                [],
                [
                    'stripe_account' => $tenant->stripeAccount()
                ]
            );

            $paymentMethod->user()->dissociate();
            $paymentMethod->save();

            $request->session()->flash('flash_bag.' . $type . '.success', 'We have deleted ' . PaymentMethod::formatName($pm) . ' from your list of payment methods.');
        } catch (\Exception $e) {
            $request->session()->flash('flash_bag.' . $type . '.error', $e->getMessage());
        }

        return Redirect::route('payments.methods.index');
    }
}
