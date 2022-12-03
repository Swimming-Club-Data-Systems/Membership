<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function showPaymentSettings()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        return Inertia::render('Settings/Payments', [
            'form_initial_values' => [
                'use_v2' => (bool)$tenant->use_payments_v2,
                'enable_automated_billing_system' => (bool)$tenant->getOption('ENABLE_BILLING_SYSTEM'),
                'hide_squad_fees_from_move_emails' => (bool)$tenant->getOption('HIDE_MOVE_FEE_INFO'),
                'squad_fee_calculation_date' => $tenant->squad_fee_calculation_date,
                'fee_calculation_date' => $tenant->fee_calculation_date,
                'billing_date' => $tenant->billing_date,
                'allow_user_billing_date_override_by_admin' => (bool)$tenant->allow_user_billing_date_override_by_admin,
                'allow_user_billing_date_override_by_user' => (bool)$tenant->allow_user_billing_date_override_by_user,
                'allow_account_top_up' => (bool)$tenant->allow_account_top_up,
                'membership_payment_methods' => [
                    'account' => (bool)$tenant->membership_payment_methods?->account,
                    'card' => (bool)$tenant->membership_payment_methods?->card,
                    'bacs_debit' => (bool)$tenant->membership_payment_methods?->bacs_debit,
                ],
                'gala_entry_payment_methods' => [
                    'account' => (bool)$tenant->gala_entry_payment_methods?->account,
                    'card' => (bool)$tenant->gala_entry_payment_methods?->card,
                    'bacs_debit' => (bool)$tenant->gala_entry_payment_methods?->bacs_debit,
                ],
                'balance_payment_methods' => [
                    'card' => (bool)$tenant->balance_payment_methods?->card,
                    'bacs_debit' => (bool)$tenant->balance_payment_methods?->bacs_debit,
                ]
            ]
        ]);
    }

    public function updatePaymentSettings(Request $request)
    {

    }
}
