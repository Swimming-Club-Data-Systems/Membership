<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\StripePaymentIntentStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
            'line_items' => $lines
        ]);
    }
}
