<?php

namespace App\Http\Controllers\Central;

use App\Business\Helpers\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    public function create(Tenant $tenant)
    {
        return Inertia::render('Central/Billing/CreatePaymentMethod', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
            'intent' => $tenant->createSetupIntent([
                'payment_method_types' => [
                    'card',
                ]
            ]),
            'stripe_publishable' => config('cashier.key')
        ]);
    }

    public function delete(Tenant $tenant, $id, Request $request)
    {
        try {
            $paymentMethod = $tenant->findPaymentMethod($id);

            $tenant->deletePaymentMethod($paymentMethod->id);

            $request->session()->flash('flash_bag.payment_method.success', 'We have deleted ' . PaymentMethod::formatName($paymentMethod) . ' from your list of payment methods.');
        } catch (\Exception $e) {
            $request->session()->flash('flash_bag.payment_method.error', $e->getMessage());
        }

        return Redirect::route('central.tenants.billing', $tenant);
    }

    public function setDefault(Tenant $tenant, $id, Request $request)
    {
        try {
            $paymentMethod = $tenant->findPaymentMethod($id);

            if ($request->input('set_default')) {
                $tenant->updateDefaultPaymentMethod($paymentMethod->id);

                $request->session()->flash('flash_bag.payment_method.success', 'We have set ' . PaymentMethod::formatName($paymentMethod) . ' as your default payment method.');
            }
        } catch (\Exception $e) {
            $request->session()->flash('flash_bag.payment_method.error', $e->getMessage());
        }

        return Redirect::route('central.tenants.billing', $tenant);
    }
}
