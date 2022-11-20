<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $directDebits = $user->paymentMethods()->where('type', '=', 'bacs_debit')->get();
        $otherMethods = $user->paymentMethods()->where('type', '!=', 'bacs_debit')->get();

        $map = function (\App\Models\Tenant\PaymentMethod $item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'description' => PaymentMethod::formatNameFromData($item->type, $item->pm_type_data),
                'created' => $item->created,
                'info_line' => PaymentMethod::formatInfoLineFromData($item->type, $item->pm_type_data),
                'default' => false,
            ];
        };

        return Inertia::render('Payments/PaymentMethods', [
            'payment_method' => null,
            'direct_debits' => $directDebits->map($map),
            'payment_methods' => $otherMethods->map($map),
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
            'success_url' => route('payments.methods.new_success'),
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
            'success_url' => route('payments.methods.new_success'),
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

    public function addPaymentMethodSuccess()
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        return Inertia::location(route('payments.methods.index'));
    }

    public function setDefault(Tenant $tenant, $id, Request $request)
    {
        try {
            $paymentMethod = $tenant->findPaymentMethod($id);

            if ($request->input('set_default')) {
                $tenant->updateDefaultPaymentMethod($paymentMethod->id);
                $tenant->save();

                $request->session()->flash('flash_bag.payment_method.success', 'We have set ' . PaymentMethod::formatName($paymentMethod) . ' as your default payment method.');
            }
        } catch (\Exception $e) {
            $request->session()->flash('flash_bag.payment_method.error', $e->getMessage());
        }

        return Redirect::route('central.tenants.billing', $tenant);
    }

    public function update($id)
    {

    }

    public function delete(\App\Models\Tenant\PaymentMethod $paymentMethod, Request $request)
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $type = $paymentMethod->type == "bacs_debit" ? 'direct_debit' : 'payment_method';

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
