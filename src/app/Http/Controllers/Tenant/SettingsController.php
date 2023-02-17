<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Gate::authorize('manage-settings');
        return Redirect::to('/v1/settings');
    }

    public function showPaymentSettings()
    {
        Gate::authorize('manage-settings');

        /** @var Tenant $tenant */
        $tenant = tenant();

        return Inertia::render('Settings/Payments', [
            'form_initial_values' => [
                'use_payments_v2' => (bool)$tenant->use_payments_v2,
                'enable_automated_billing_system' => (bool)$tenant->getOption('ENABLE_BILLING_SYSTEM'),
                'hide_squad_fees_from_move_emails' => (bool)$tenant->getOption('HIDE_MOVE_FEE_INFO'),
                'squad_fee_calculation_date' => $tenant->squad_fee_calculation_date ?? 1,
                'fee_calculation_date' => $tenant->fee_calculation_date ?? 1,
                'billing_date' => $tenant->billing_date ?? 1,
                'allow_user_billing_date_override_by_admin' => (bool)$tenant->allow_user_billing_date_override_by_admin,
                'allow_user_billing_date_override_by_user' => (bool)$tenant->allow_user_billing_date_override_by_user,
                'allow_account_top_up' => (bool)$tenant->allow_account_top_up,
                'membership_payment_methods' => [
                    'account' => (bool)$tenant->membership_payment_methods_account,
                    'card' => (bool)$tenant->membership_payment_methods_card,
                    'bacs_debit' => (bool)$tenant->membership_payment_methods_bacs_debit,
                ],
                'gala_entry_payment_methods' => [
                    'account' => (bool)$tenant->gala_entry_payment_methods_account,
                    'card' => (bool)$tenant->gala_entry_payment_methods_card,
                    'bacs_debit' => (bool)$tenant->gala_entry_payment_methods_bacs_debit,
                ],
                'balance_payment_methods' => [
                    'card' => (bool)$tenant->balance_payment_methods_card,
                    'bacs_debit' => (bool)$tenant->balance_payment_methods_bacs_debit,
                ]
            ]
        ]);
    }

    public function updatePaymentSettings(Request $request)
    {
        Gate::authorize('manage-settings');

        $request->validate([
            'use_payments_v2' => ['boolean'],
            'enable_automated_billing_system' => ['boolean'],
            'hide_squad_fees_from_move_emails' => ['boolean'],
            'squad_fee_calculation_date' => ['required', 'integer', 'between:1,28'],
            'fee_calculation_date' => ['required', 'integer', 'between:1,28'],
            'billing_date' => ['required', 'integer', 'between:1,28'],
            'allow_user_billing_date_override_by_admin' => ['boolean'],
            'allow_user_billing_date_override_by_user' => ['boolean'],
            'allow_account_top_up' => ['boolean'],
            'membership_payment_methods.account' => ['boolean'],
            'membership_payment_methods.card' => ['boolean'],
            'membership_payment_methods.bacs_debit' => ['boolean'],
            'gala_entry_payment_methods.account' => ['boolean'],
            'gala_entry_payment_methods.card' => ['boolean'],
            'gala_entry_payment_methods.bacs_debit' => ['boolean'],
            'balance_payment_methods.card' => ['boolean'],
            'balance_payment_methods.bacs_debit' => ['boolean'],
        ]);

        /** @var Tenant $tenant */
        $tenant = tenant();

        $tenant->use_payments_v2 = $request->boolean('use_payments_v2');
        $tenant->setOption('ENABLE_BILLING_SYSTEM', $request->boolean('enable_automated_billing_system'));
        $tenant->setOption('HIDE_MOVE_FEE_INFO', $request->boolean('hide_squad_fees_from_move_emails'));
        $tenant->squad_fee_calculation_date = $request->input('squad_fee_calculation_date');
        $tenant->fee_calculation_date = $request->input('fee_calculation_date');
        $tenant->billing_date = $request->input('billing_date');
        $tenant->allow_user_billing_date_override_by_admin = $request->boolean('allow_user_billing_date_override_by_admin');
        $tenant->allow_user_billing_date_override_by_user = $request->boolean('allow_user_billing_date_override_by_user');
        $tenant->allow_account_top_up = $request->boolean('allow_account_top_up');
        $tenant->membership_payment_methods_account = $request->boolean('membership_payment_methods.account');
        $tenant->membership_payment_methods_card = $request->boolean('membership_payment_methods.card');
        $tenant->membership_payment_methods_bacs_debit = $request->boolean('membership_payment_methods.bacs_debit');
        $tenant->gala_entry_payment_methods_account = $request->boolean('gala_entry_payment_methods.account');
        $tenant->gala_entry_payment_methods_card = $request->boolean('gala_entry_payment_methods.card');
        $tenant->gala_entry_payment_methods_bacs_debit = $request->boolean('gala_entry_payment_methods.bacs_debit');
        $tenant->balance_payment_methods_card = $request->boolean('balance_payment_methods.card');
        $tenant->balance_payment_methods_bacs_debit = $request->boolean('balance_payment_methods.bacs_debit');

        $tenant->save();

        return Redirect::route('settings.payments');
    }
}
