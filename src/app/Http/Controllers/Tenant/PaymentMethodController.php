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
        return Inertia::render('Payments/PaymentMethods', [
            'payment_method' => null,
            'payment_methods' => [],
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

    public function delete($id)
    {

    }
}
