<?php

namespace App\Jobs\StripeWebhooks;

use App\Models\Central\Tenant;
use App\Models\Tenant\Price;
use App\Traits\JobBackoff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookClient\Models\WebhookCall;

class HandlePriceDeleted implements ShouldQueue
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

            try {
                DB::beginTransaction();

                $price = Price::where('stripe_id', '=', $this->webhookCall->payload['data']['object']['id'])->first();

                $price?->delete();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
            }

        });
    }
}
