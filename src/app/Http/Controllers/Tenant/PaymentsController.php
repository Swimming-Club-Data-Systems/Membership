<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Enums\StripePaymentIntentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\PaidObject;
use App\Mail\Payments\PaymentRefunded;
use App\Models\Central\Tenant;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use App\Models\Tenant\Refund;
use App\Models\Tenant\User;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        $data = $this->indexData($user);

        return Inertia::render('Payments/Payments/Index', $data);
    }

    private function indexData(User $user): array
    {
        $payments = $user->payments()
            ->where('stripe_status', StripePaymentIntentStatus::SUCCEEDED)
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

        return [
            'payments' => $payments->onEachSide(3),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ];
    }

    public function userIndex(User $user): \Inertia\Response
    {
        $this->authorize('viewIndex', Payment::class);

        $data = $this->indexData($user);

        return Inertia::render('Payments/Payments/UserIndex', $data);
    }

    public function show(Payment $payment): \Inertia\Response
    {
        $this->authorize('view', $payment);

        $data = $this->showData($payment);

        return Inertia::render('Payments/Payments/Payment', $data);
    }

    private function showData(Payment $payment): array
    {
        $lines = [];
        $refunds = [];

        $formInitialValues = [
            'reason' => 'n/a',
            'lines' => [],
        ];

        foreach ($payment->lines()->with(['refunds'])->get() as $line) {
            /** @var PaymentLine $line */
            $lineRefunds = [];
            foreach ($line->refunds()->get() as $refund) {
                /** @var Refund $refund */
                $lineRefunds[] = [
                    'id' => $refund->id,
                    'amount' => $refund->amount,
                    'formatted_amount' => $refund->formatted_amount,
                    'line_refund_amount' => $refund->pivot->amount,
                    'formatted_line_refund_amount' => $refund->pivot->formatted_amount,
                    'line_refund_description' => $refund->pivot->description,
                    'created_at' => $refund->created_at,
                ];
            }

            $lines[] = [
                'id' => $line->id,
                'description' => $line->description,
                'formatted_amount_total' => $line->formatted_amount_total,
                'formatted_amount_discount' => $line->formatted_amount_discount,
                'formatted_amount_refunded' => $line->formatted_amount_refunded,
                'formatted_amount_subtotal' => $line->formatted_amount_subtotal,
                'formatted_amount_tax' => $line->formatted_amount_tax,
                'formatted_unit_amount' => $line->formatted_unit_amount,
                'amount_refundable' => Money::formatDecimal($line->amount_total - $line->amount_refunded),
                'amount_refundable_int' => $line->amount_total - $line->amount_refunded,
                'amount_refunded_int' => $line->amount_refunded,
                'quantity' => $line->quantity,
                'refunds' => $lineRefunds,
            ];

            $formInitialValues['lines'][] = [
                'id' => $line->id,
                'refund_amount' => '',
                'currency' => $line->currency,
                'amount_refunded' => Money::formatDecimal($line->amount_refunded, $line->currency),
                'amount_refunded_int' => $line->amount_refunded,
                'amount_refundable' => Money::formatDecimal($line->amount_total - $line->amount_refunded),
                'amount_refundable_int' => $line->amount_total - $line->amount_refunded,
            ];
        }

        foreach ($payment->refunds()->with(['lines'])->get() as $refund) {
            /** @var Refund $refund */
            $refundLines = [];
            foreach ($refund->lines()->get() as $line) {
                /** @var PaymentLine $line */
                $refundLines[] = [
                    'id' => $line->id,
                ];
            }

            $refunds[] = [
                'id' => $refund->id,
                'refunder' => [
                    'id' => $refund->refunder->id,
                    'name' => $refund->refunder->name,
                ],
                'stripe_id' => $refund->stripe_id,
                'status' => $refund->status,
                'reason' => $refund->reason,
                'failure_reason' => $refund->failure_reason,
                'amount' => $refund->amount,
                'formatted_amount' => $refund->formatted_amount,
                'currency' => $refund->currency,
                'created_at' => $refund->created_at,
                'lines' => $refundLines,
            ];
        }

        /** @var User $loggedInUser */
        $loggedInUser = Auth::user();

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
            'user' => [
                'id' => $payment->user?->id,
                'name' => $payment->user?->name,
            ],
            'line_items' => $lines,
            'refunds' => $refunds,
            'form_initial_values' => $formInitialValues,
            'is_administrator' => $loggedInUser->can('refund', $payment),
        ];
    }

    public function userShow(User $user, Payment $payment): \Inertia\Response
    {
        $this->authorize('view', $payment);

        $data = $this->showData($payment);

        return Inertia::render('Payments/Payments/UserPayment', $data);
    }

    /**
     * @throws ValidationException
     */
    public function refund(Payment $payment, Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $request->validate([
            'reason' => ['required', Rule::in(['n/a', 'duplicate', 'fraudulent', 'requested_by_customer'])],
            'lines.*.refund_amount' => ['nullable', 'min:0', 'decimal:0,2'],
        ]);

        $lines = $request->input('lines');

        $refundTotal = 0;
        foreach ($payment->lines as $line) {
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

        if ($refundTotal <= 0) {
            throw ValidationException::withMessages([
                'total' => 'The total to refund must be greater than zero.',
            ]);
        }

        /** @var Tenant $tenant */
        $tenant = tenant();

        $stripe = new \Stripe\StripeClient([
            'api_key' => config('cashier.secret'),
            'stripe_version' => '2022-11-15',
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'payment_intent' => $payment->stripe_id,
                'amount' => $refundTotal,
            ];

            // Set instruction email if required by payment method
            // if (some_condition) {
            //     $data['instructions_email'] = $payment->user?->email ?? $payment->receipt_email;
            // }

            // Add a reason if provided
            if ($request->input('reason') != 'n/a') {
                $data['reason'] = $request->input('reason');
            }

            $refund = $stripe->refunds->create($data, [
                'stripe_account' => $tenant->stripeAccount(),
            ]);

            if ($refund->status == 'succeeded' || $refund->status == 'pending') {
                /** @var Refund $dbRefund */
                $dbRefund = Refund::firstOrNew([
                    'stripe_id' => $refund->id,
                ], [
                    'payment' => $payment,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                    'currency' => $refund->currency,
                ]);
                $dbRefund->refunder()->associate(Auth::user());
                $dbRefund->created_at = $refund->created;
                $dbRefund->payment()->associate($payment);
                $dbRefund->save();

                // Refund all paid objects and save changes in the database
                foreach ($payment->lines as $line) {
                    /** @var PaymentLine $line */
                    foreach ($lines as $postedLine) {
                        if ($postedLine['id'] == $line->id && $postedLine['refund_amount']) {
                            $amount = BigDecimal::of($postedLine['refund_amount'])->withPointMovedRight(2)->toInt();

                            $line->amount_refunded = $line->amount_refunded + $amount;
                            if ($line->associated && $line->associated instanceof PaidObject) {
                                $line->associated->handleRefund($amount, $line->amount_refunded);
                            } elseif ($line->associatedUuid && $line->associatedUuid instanceof PaidObject) {
                                $line->associatedUuid->handleRefund($amount, $line->amount_refunded);
                            }
                            $line->save();

                            $dbRefund->lines()->attach($line, [
                                'amount' => $amount,
                            ]);
                        }
                    }
                }

                $payment->amount_refunded = $payment->amount_refunded + $refund->amount;
                $payment->save();

                $refundFormatted = Money::formatCurrency($refund->amount, $refund->currency);
                $totalRefundedFormatted = Money::formatCurrency($payment->amount_refunded, $refund->currency);
                $request->session()->flash('success', "We've refunded {$refundFormatted} to the original payment method ({$payment->paymentMethod->description}). The total amount refunded is {$totalRefundedFormatted}.");

                try {
                    if ($payment->user) {
                        Mail::to($payment->user)->send(new PaymentRefunded($payment, $refund->amount));
                    }
                } catch (\Exception $e) {
                    // Need to catch the error, but if the email fails, that must not stop the data being committed
                }
            }

            DB::commit();

        } catch (ApiErrorException $e) {
            DB::rollBack();
            $request->session()->flash('error', 'Something went wrong which meant we could not refund this payment. Please try again later.');
            report($e);
        }

        return redirect(route('payments.payments.show', $payment));
    }
}
