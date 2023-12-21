<?php

namespace App\Jobs\StripeWebhooks;

use App\Enums\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

/**
 * Handle a connected account revoking permission to the SCDS connect application
 */
class HandleAccountApplicationDeauthorized implements ShouldQueue
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
        // $this->onQueue(Queue::STRIPE->value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Remove Stripe account id from the system to disable all Stripe services in the tenant app
        // Store old Stripe account id so that if set up again we can check it's the same and fail otherwise
        // Email the tenant admins telling them services have been disconnected and are now unavailable
        // Include note that they can only sign up again with the same account as resources in DB belong to it
    }
}
