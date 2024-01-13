<?php

namespace App\Jobs\StripeWebhooks;

use App\Events\Tenant\PointOfSale\ReaderActionFailed;
use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleTerminalReaderActionFailed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public WebhookCall $webhookCall;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $webhookCallId
    ) {
        //
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

                $action = $this->webhookCall->payload['data']['object']['action'];

                dump($action);

                if ($action) {

                    if ($action['status'] === 'failed') {
                        ReaderActionFailed::dispatch([
                            'reader_id' => $this->webhookCall->payload['data']['object']['id'],
                            'status' => $action['status'],
                            'failure_code' => $action['failure_code'],
                            'failure_message' => $action['failure_message'],
                        ]);
                    }

                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
            }

        });
    }
}
