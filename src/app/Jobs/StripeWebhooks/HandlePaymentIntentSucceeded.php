<?php

namespace App\Jobs\StripeWebhooks;

use App\Enums\PaymentStatus;
use App\Enums\Queue;
use App\Interfaces\PaidObject;
use App\Mail\Payments\PaymentSucceeded;
use App\Models\Central\Tenant;
use App\Models\Tenant\JournalAccount;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\WebhookClient\Models\WebhookCall;

class HandlePaymentIntentSucceeded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public WebhookCall $webhookCall
    ) {
        $this->onQueue(Queue::STRIPE->value);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tenant = Tenant::findByStripeAccountId($this->webhookCall->payload['account']);

        $tenant->run(function () {

            try {
                DB::beginTransaction();
                $intent = \Stripe\PaymentIntent::retrieve([
                    'id' => $this->webhookCall->payload['data']['object']['id'],
                    'expand' => ['customer', 'payment_method', 'charges.data.balance_transaction'],
                ], [
                    'stripe_account' => $this->webhookCall->payload['account'],
                ]);

                if ($intent?->metadata?->payment_id) {
                    // Try and find a payment with this id
                    /** @var Payment $payment */
                    $payment = Payment::find($intent->metadata->payment_id);

                    if ($payment && $payment->status != PaymentStatus::SUCCEEDED) {

                        $payment->status = PaymentStatus::SUCCEEDED;
                        $payment->stripe_status = $intent->status;
                        foreach ($payment->lines()->with(['associated', 'associatedUuid'])->get() as $line) {
                            /** @var PaymentLine $line */
                            if ($line->associated && $line->associated instanceof PaidObject) {
                                $line->associated->handlePaid($line);
                            } elseif ($line->associatedUuid && $line->associatedUuid instanceof PaidObject) {
                                $line->associatedUuid->handlePaid($line);
                            }

                            $associate = $line->associated ?? $line; // Not including uuid as it can't be referenced this way

                            // Debit the user/guest journal (already done if monthly fees)
                            if ($intent->metadata->payment_category != 'monthly_fee') {
                                if ($payment->user) {
                                    $payment->user->getJournal();
                                    $journal = $payment->user->journal;
                                    $transaction = $journal->debit($line->amount_total, $line->description);
                                    $transaction->referencesObject($associate);
                                } else {
                                    /** @var JournalAccount $guestIncomeJournal */
                                    $guestIncomeJournal = JournalAccount::firstWhere([
                                        'name' => 'Guest Customers',
                                        'is_system' => true,
                                    ]);
                                    $transaction = $guestIncomeJournal
                                        ->journal
                                        ->debit($line->amount_total, $line->description);
                                    $transaction->referencesObject($associate);
                                }
                            }
                        }
                        $type = $intent->payment_method->type;

                        $paymentMethod = PaymentMethod::firstOrCreate(
                            ['stripe_id' => $intent->payment_method->id],
                            [
                                'type' => $type,
                                'pm_type_data' => $intent->payment_method->$type,
                                'billing_address' => $intent->payment_method->billing_details,
                                'created_at' => $intent->payment_method->created,
                            ],
                        );

                        $payment->paymentMethod()->associate($paymentMethod);
                        $payment->save();

                        // Credit the user's journal with the amount paid
                        if ($payment->user) {
                            $payment->user->getJournal();
                            $journal = $payment->user->journal;
                            $transaction = $journal->credit($payment->amount, 'Payment received with thanks');

                            // Trigger Email Receipt
                            Mail::to($payment->user)->send(new PaymentSucceeded($payment));
                        } else {
                            // Credit the guest journal
                            /** @var JournalAccount $guestIncomeJournal */
                            $guestIncomeJournal = JournalAccount::firstWhere([
                                'name' => 'Guest Customers',
                                'is_system' => true,
                            ]);
                            $transaction = $guestIncomeJournal->journal->credit($payment->amount, 'Payment received with thanks');

                            try {
                                // Trigger Email Receipt
                                Mail::to($payment)->send(new PaymentSucceeded($payment));
                            } catch (\Exception $e) {
                                report($e);
                                // Can't send, ignore silently
                            }
                        }
                        $transaction->referencesObject($payment);

                        DB::commit();
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
            }

        });
    }
}
