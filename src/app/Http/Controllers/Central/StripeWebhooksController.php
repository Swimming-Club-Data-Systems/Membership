<?php

namespace App\Http\Controllers\Central;

use App\Business\Webhooks\StripeWebhookProcessor;
use App\Jobs\StripeWebhooks\ProcessStripeWebhookJob;
use Illuminate\Http\Request;
use Spatie\StripeWebhooks\StripeSignatureValidator;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;

class StripeWebhooksController
{
    //    public function __invoke(Request $request, ?string $configKey = null)
    //    {
    //        $webhookConfig = new WebhookConfig([
    //            'name' => 'stripe',
    //            'signing_secret' => ($configKey) ?
    //                config('stripe-webhooks.signing_secret_'.$configKey) :
    //                config('stripe-webhooks.signing_secret'),
    //            'signature_header_name' => 'Stripe-Signature',
    //            'signature_validator' => StripeSignatureValidator::class,
    //            'webhook_profile' => config('stripe-webhooks.profile'),
    //            'webhook_model' => config('stripe-webhooks.model'),
    //            'process_webhook_job' => ProcessStripeWebhookJob::class,
    //        ]);
    //
    //        return (new WebhookProcessor($request, $webhookConfig))->process();
    //    }

    public function __invoke(Request $request, ?string $configKey = null)
    {
        try {
            $webhookConfig = new WebhookConfig([
                'name' => 'stripe',
                'signing_secret' => ($configKey) ?
                    config('stripe-webhooks.signing_secret_'.$configKey) :
                    config('stripe-webhooks.signing_secret'),
                'signature_header_name' => 'Stripe-Signature',
                'signature_validator' => StripeSignatureValidator::class,
                'webhook_profile' => config('stripe-webhooks.profile'),
                'webhook_model' => config('stripe-webhooks.model'),
                'process_webhook_job' => ProcessStripeWebhookJob::class,
            ]);

            return (new StripeWebhookProcessor($request, $webhookConfig))->process();
        } catch (InvalidConfig $e) {
            abort(501, 'Stripe is not configured on this SCDS Membership instance.');
        }
    }
}
