<?php

namespace App\Jobs\StripeWebhooks;

use App\Models\Central\Tenant;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class HandlePaymentMethodUpdated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Find the payment method if it's in the database

        /** @var Tenant $tenant */
        $tenant = Tenant::findByStripeAccountId($this->webhookCall->payload['account']);

        $tenant->run(function () {
            \Stripe\Stripe::setApiKey(config('cashier.secret'));

            /** @var PaymentMethod $paymentMethod */
            $paymentMethod = PaymentMethod::firstWhere('stripe_id', '=', $this->webhookCall->payload['data']['object']['id']);

            $paymentMethod?->updateStripeData();
        });
    }
}