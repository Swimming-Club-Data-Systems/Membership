<?php

namespace App\Jobs\StripeWebhooks;

use App\Enums\Queue;
use App\Enums\StripeDisputeStatus;
use App\Models\Central\Tenant;
use App\Models\Tenant\JournalAccount;
use App\Models\Tenant\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleChargeDisputeClosed implements ShouldQueue
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

            $dispute = \Stripe\Dispute::retrieve([
                'id' => $this->webhookCall->payload['data']['object']['id'],
                'expand' => ['payment_intent'],
            ], [
                'stripe_account' => $this->webhookCall->payload['account'],
            ]);

            if ($dispute?->payment_intent?->metadata?->payment_id) {
                // Try and find a payment with this id
                /** @var Payment $payment */
                $payment = Payment::find($dispute?->payment_intent?->metadata?->payment_id);

                if ($payment && $dispute->status == StripeDisputeStatus::WON) {
                    DB::beginTransaction();

                    // We will immediately credit the appropriate journal as the customer lost the dispute
                    if ($payment->user) {
                        $payment->user->getJournal();
                        $journal = $payment->user->journal;
                        $transaction = $journal->credit($dispute->amount, 'Payment Dispute Lost');
                    } else {
                        // Credit the guest journal
                        $guestIncomeJournal = JournalAccount::firstWhere([
                            'name' => 'Guest Customers',
                            'is_system' => true,
                        ]);
                        $transaction = $guestIncomeJournal->credit($dispute->amount, 'Payment Dispute Lost');
                    }
                    $transaction->referencesObject($payment);

                    DB::commit();
                }
            }

        });
    }
}
