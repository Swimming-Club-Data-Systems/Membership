<?php

namespace App\Jobs\StripeWebhooks;

use App\Models\Central\Tenant;
use App\Models\Tenant\Product;
use App\Traits\JobBackoff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookClient\Models\WebhookCall;

class HandlePriceUpdated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, JobBackoff, Queueable, SerializesModels;

    public WebhookCall $webhookCall;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public int $webhookCallId
    ) {
        // $this->onQueue(Queue::STRIPE->value);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->webhookCall = WebhookCall::findOrFail($this->webhookCallId);

        $tenant = Tenant::findByStripeAccountId($this->webhookCall->payload['account']);

        $tenant->run(function () {

            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            try {
                DB::beginTransaction();
                $stripePrice = $stripe->prices->retrieve($this->webhookCall->payload['data']['object']['id'], [
                ], [
                    'stripe_account' => $this->webhookCall->payload['account'],
                ]);

                // Find product
                /** @var Product $product */
                $product = Product::where('stripe_id', '=', $stripePrice->product)->first();

                if (! $product) {
                    // Create one
                    $product = Product::fromStripe($stripePrice->product);
                }

                $product->prices()->updateOrCreate([
                    'stripe_id' => $stripePrice->id,
                ], [
                    'currency' => $stripePrice->currency,
                    'active' => $stripePrice->active,
                    'nickname' => $stripePrice->nickname,
                    'type' => $stripePrice->type,
                    'unit_amount' => $stripePrice->unit_amount,
                    'billing_scheme' => $stripePrice->billing_scheme,
                    'tax_behavior' => $stripePrice->tax_behavior,
                    'usable_in_membership' => true,
                ]);

                //                $price = Price::firstOrCreate([
                //                    'stripe_id' => $stripePrice->id,
                //                ], [
                //                    'currency' => $stripePrice->currency,
                //                    'active' => $stripePrice->active,
                //                    'nickname' => $stripePrice->nickname,
                //                    'type' => $stripePrice->type,
                //                    'unit_amount' => $stripePrice->unit_amount,
                //                    'billing_scheme' => $stripePrice->billing_scheme,
                //                    'tax_behavior' => $stripePrice->tax_behavior,
                //                    'usable_in_membership' => true,
                //                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
            }

        });
    }
}
