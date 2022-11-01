<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;

class InvoiceController extends Controller
{
    public function show($invoiceId, Request $request)
    {
        \Stripe\Stripe::setApiKey(config('cashier.secret'));

        $invoice = null;
        try {
            $invoice = \Stripe\Invoice::retrieve($invoiceId);
        } catch (ApiErrorException $e) {
            abort(404);
        }

        return Inertia::render('Central/Billing/Invoice', [
            'invoice' => $invoice,
            'lines' => $invoice->lines->data,
        ]);
    }

    public function download(Tenant $tenant, $invoice)
    {
        return $tenant->downloadInvoice($invoice);
    }
}
