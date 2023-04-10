<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Enums\StripePaymentIntentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\PaidObject;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use App\Models\Tenant\User;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;

class PaymentsController extends Controller
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

    public function index(): \Inertia\Response
    {
        /** @var User $user */
        $user = Auth::user();

        $payments = Payment::where('stripe_status', StripePaymentIntentStatus::SUCCEEDED)
            ->orderBy('created_at', 'desc')
            ->with(['paymentMethod'])
            ->paginate();

        $payments->getCollection()->transform(function (Payment $payment) {
            return [
                'id' => $payment->id,
                'payment_method' => [
                    'id' => $payment->paymentMethod?->id,
                    'description' => $payment->paymentMethod?->description,
                    'information_line' => $payment->paymentMethod?->information_line,
                    'type' => $payment->paymentMethod?->type,
                ],
                'stripe_id' => $payment->stripe_id,
                'stripe_status' => $payment->stripe_status,
                'amount' => $payment->amount,
                'amount_refunded' => $payment->amount_refunded,
                'formatted_amount' => $payment->formatted_amount,
                'formatted_amount_refunded' => $payment->formatted_amount_refunded,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
            ];
        });

        $data = [
            'payments' => $payments->onEachSide(3),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ];

        return Inertia::render('Payments/Payments/Index', $data);
    }

    public function show(Payment $payment): \Inertia\Response
    {
        $lines = [];

        $formInitialValues = [
            'reason' => 'n/a',
            'lines' => [],
        ];

        foreach ($payment->lines()->get() as $line) {
            /** @var PaymentLine $line */
            $lines[] = [
                'id' => $line->id,
                'description' => $line->description,
                'formatted_amount_total' => $line->formatted_amount_total,
                'formatted_amount_discount' => $line->formatted_amount_discount,
                'formatted_amount_refunded' => $line->formatted_amount_refunded,
                'formatted_amount_subtotal' => $line->formatted_amount_subtotal,
                'formatted_amount_tax' => $line->formatted_amount_tax,
                'formatted_unit_amount' => $line->formatted_unit_amount,
                'quantity' => $line->quantity,
            ];

            $formInitialValues['lines'][] = [
                'id' => $line->id,
                'refund_amount' => "",
                'currency' => $line->currency,
                'amount_refunded' => Money::formatDecimal($line->amount_refunded, $line->currency),
                'amount_refunded_int' => $line->amount_refunded,
                'amount_refundable' => Money::formatDecimal($line->amount_total - $line->amount_refunded),
                'amount_refundable_int' => $line->amount_total - $line->amount_refunded,
            ];
        }

        return Inertia::render('Payments/Payments/Payment', [
            'id' => $payment->id,
            'payment_method' => [
                'id' => $payment->paymentMethod?->id,
                'description' => $payment->paymentMethod?->description,
                'information_line' => $payment->paymentMethod?->information_line,
                'type' => $payment->paymentMethod?->type,
            ],
            'stripe_id' => $payment->stripe_id,
            'stripe_status' => $payment->stripe_status,
            'amount' => $payment->amount,
            'amount_refunded' => $payment->amount_refunded,
            'formatted_amount' => $payment->formatted_amount,
            'formatted_amount_refunded' => $payment->formatted_amount_refunded,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
            'user' => [
                'id' => $payment->user?->id,
                'name' => $payment->user?->name,
            ],
            'line_items' => $lines,
            'form_initial_values' => $formInitialValues,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function refund(Payment $payment, Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $request->validate([
            'reason' => ['required', Rule::in(['n/a', 'duplicate', 'fraudulent', 'requested_by_customer'])],
            'lines.*.refund_amount' => ['min:0', 'decimal:0,2'],
        ]);

        $lines = $request->input('lines');

        $refundTotal = 0;
        foreach ($payment->lines()->get() as $line) {
            /** @var PaymentLine $line */
            foreach ($lines as $postedLine) {
                if ($postedLine['id'] == $line->id && $postedLine['refund_amount']) {
                    $amount = BigDecimal::of($postedLine['refund_amount'])->withPointMovedRight(2)->toInt();

                    if ($amount > $line->amount_total - $line->amount_refunded) {
                        throw ValidationException::withMessages([
                            'lines[x].amount_refundable' => 'Amount to refund is greater than the refundable amount.',
                        ]);
                    }

                    $refundTotal += $amount;
                    break;
                }
            }
        }

        if ($refundTotal > ($payment->amount - $payment->amount_refunded)) {
            throw ValidationException::withMessages([
                'total' => 'Amount to refund is greater than the refundable amount.',
            ]);
        }

        /** @var Tenant $tenant */
        $tenant = tenant();

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
        try {
            $data = [
                'payment_intent' => $payment->stripe_id,
                'amount' => $refundTotal,
                'instructions_email' => $payment->user?->email ?? $payment->receipt_email,
            ];

            // Add a reason if provided
            if ($request->input('reason') != "n/a") {
                $data['reason'] = $request->input('reason');
            }

            $refund = $stripe->refunds->create($data, [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            if ($refund->status == 'succeeded' || $refund->status == 'pending') {
                // Refund all paid objects and save changes in the database
                foreach ($payment->lines()->get() as $line) {
                    /** @var PaymentLine $line */
                    foreach ($lines as $postedLine) {
                        if ($postedLine['id'] == $line->id && $postedLine['refund_amount']) {
                            $amount = BigDecimal::of($postedLine['refund_amount'])->withPointMovedRight(2)->toInt();

                            $line->amount_refunded = $line->amount_refunded + $amount;
                            if ($line->associated && $line->associated instanceof PaidObject) {
                                $line->associated->handleRefund($amount);
                            }
                            $line->save();
                        }
                    }
                }

                $payment->amount_refunded = $payment->amount_refunded + $refund->amount;
                $payment->save();

                $refundFormatted = Money::formatCurrency($refund->amount, $refund->currency);
                $totalRefundedFormatted = Money::formatCurrency($payment->amount_refunded, $refund->currency);
                $request->session()->flash('success', "We've refunded {$refundFormatted} to the original payment method ({$payment->paymentMethod->description}). The total amount refunded is {$totalRefundedFormatted}.");
            }

        } catch (ApiErrorException $e) {
            $request->session()->flash('error', "Something went wrong which meant we could not refund this payment. Please try again later.");
        }

        return redirect(route('payments.payments.show', $payment));
    }
}
