<?php

namespace App\Listeners;

use App\Business\Helpers\PaymentMethod;
use App\Models\Central\Tenant;
use App\Models\Tenant\Sms;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\Exception\ApiErrorException;

class StripeEventListener
{
    /**
     * Handle received Stripe webhooks.
     */
    public function handle(WebhookReceived $event): void
    {
        $stripe = new \Stripe\StripeClient(config('cashier.secret'));

        if ($event->payload['type'] === 'checkout.session.completed') {
            // Handle the incoming event...
            if ($event->payload['data']['object']['payment_status'] == 'paid' && $event->payload['data']['object']['metadata']['type'] == 'tenant_account_top_up') {
                try {
                    /** @var Tenant $tenant */
                    $tenant = Tenant::find($event->payload['data']['object']['metadata']['tenant']);

                    $paymentIntent = $stripe->paymentIntents->retrieve($event->payload['data']['object']['payment_intent'], [
                        'expand' => ['payment_method'],
                    ]);

                    $transaction = $tenant->journal->credit($paymentIntent->amount, 'Account Top Up ('.PaymentMethod::formatName($paymentIntent->payment_method).') (PI '.$paymentIntent->id.')');

                } catch (\Exception $e) {

                }
            }
        } elseif ($event->payload['type'] === 'invoice.created') {
            try {
                // Make the invoice paper size A4
                $invoice = $stripe->invoices->retrieve($event->payload['data']['object']['id']);

                $stripe->invoices->update($invoice->id,
                    [
                        'rendering' => [
                            'pdf' => [
                                'page_size' => 'a4',
                            ],
                        ],
                    ]);

                // Add period information to the invoice (e.g. SMS fees)

                /** @var Tenant $tenant */
                $tenant = Cashier::findBillable($event->payload['data']['object']['customer']);

                if ($tenant != null && $invoice->subscription == $tenant->subscription()?->stripe_id) {
                    // Get SMS messages in the period
                    $smsPeriod = Sms::where('created_at', '>=', Carbon::createFromTimestamp($invoice->period_start, 'UTC'))
                        ->where('created_at', '<', Carbon::createFromTimestamp($invoice->period_end, 'UTC'));

                    $count = $smsPeriod->sum('segments_sent');
                    $amount = $smsPeriod->sum('amount');

                    $stripe->invoiceItems->create([
                        'customer' => $tenant->stripe_id,
                        'invoice' => $invoice->id,
                        'amount' => $amount,
                        'currency' => 'gbp',
                        'description' => "Notify SMS ($count outbound message segments)",
                        'discounts' => [
                            ['coupon' => 'paygobalance'],
                        ],
                        'tax_behavior' => 'exclusive',
                        'period' => [
                            'start' => $invoice->period_start,
                            'end' => $invoice->period_end,
                        ],
                    ]);
                }

            } catch (ApiErrorException $e) {
                report($e);
            }
        }
    }
}
