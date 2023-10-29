<?php

namespace App\Jobs;

use App\Business\Helpers\ApplicationFeeAmount;
use App\Enums\BalanceTopUpStatus;
use App\Models\Central\Tenant;
use App\Models\Tenant\BalanceTopUp;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentLine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class PayChargeFees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public BalanceTopUp $topUp
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        // Create a new Payment and Payment Item for the top-up

        $payment = new Payment();
        $payment->user()->associate($this->topUp->user);
        $payment->paymentMethod()->associate($this->topUp->paymentMethod);
        $payment->application_fee_amount = ApplicationFeeAmount::calculateAmount($this->topUp->amount);
        $payment->currency = 'gbp';
        $payment->save();

        $lineItem = new PaymentLine();
        $lineItem->unit_amount = $this->topUp->amount;
        $lineItem->quantity = 1;
        $lineItem->currency = 'gbp';
        $lineItem->associated()->associate($this->topUp);
        $payment->lines()->save($lineItem);

        $payment->refresh();

        try {
            $intent = \Stripe\PaymentIntent::create([
                'payment_method_types' => ['bacs_debit'],
                'payment_method' => $this->topUp->paymentMethod->stripe_id,
                'customer' => $this->topUp->user->stripeCustomerId(),
                'confirm' => true,
                'amount' => $payment->amount,
                'currency' => 'gbp',
                'description' => "Balance Top Up (#{$this->topUp->id})",
                'metadata' => [
                    'payment_category' => 'monthly_fee',
                    'payment_id' => $payment->id,
                    'user_id' => $this->topUp->user->UserID,
                    'balance_top_up_id' => $this->topUp->id,
                ],
                'application_fee_amount' => $payment->application_fee_amount,
            ], [
                'stripe_account' => $tenant->stripeAccount(),
                'idempotency_key' => 'balance_top_up_'.$this->topUp->id,
            ]);

            $payment->stripe_id = $intent->id;
            $payment->save();

            $this->topUp->status = BalanceTopUpStatus::IN_PROGRESS;
            $this->topUp->save();
        } catch (\Exception $e) {
            report($e);
            $payment->status = 'failed';
            $payment->save();
            $this->topUp->status = BalanceTopUpStatus::FAILED;
            $this->topUp->save();
        }
    }

    /**
     * Get the middleware the job should pass through.
     *
     * Uses WithoutOverlapping so that we can not process two charges for the same user at the same time
     * This ensures multiple runs of the scheduler don't cause double charges etc
     *
     * @return array
     */
    public function middleware()
    {
        return [
            (new WithoutOverlapping($this->topUp->user->UserID))
                ->releaseAfter(60)
                ->expireAfter(180),
        ];
    }
}
