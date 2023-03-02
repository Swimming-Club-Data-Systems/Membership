<?php

namespace App\Jobs\StripeWebhooks;

use App\Enums\PaymentStatus;
use App\Enums\Queue;
use App\Interfaces\PaidObject;
use App\Models\Central\Tenant;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookClient\Models\WebhookCall;

class HandlePaymentIntentPaymentFailed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public WebhookCall $webhookCall
    )
    {
        $this->onQueue(Queue::STRIPE->value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tenant = Tenant::findByStripeAccountId($this->webhookCall->payload['account']);

        $tenant->run(function () {

            $intent = \Stripe\PaymentIntent::retrieve([
                'id' => $this->webhookCall->payload['data']['object']['id'],
                'expand' => ['customer', 'payment_method', 'charges.data.balance_transaction'],
            ], [
                'stripe_account' => $this->webhookCall->payload['account']
            ]);

            if ($intent?->metadata?->payment_id) {
                // Try and find a payment with this id
                /** @var Payment $payment */
                $payment = Payment::findOrFail($intent->metadata->payment_id);

                if ($payment->status != PaymentStatus::FAILED) {
                    DB::beginTransaction();

                    $payment->status = PaymentStatus::FAILED;
                    $payment->stripe_status = $intent->status;
                    foreach ($payment->lines()->get() as $line) {
                        /** @var PaymentLine $line */
                        if ($line->associated && $line->associated instanceof PaidObject) {
                            $line->associated->handleFailed();
                        }
                    }

                    DB::commit();
                }
            }

            // TODO Trigger Payment Failure Email

        });
    }
}
