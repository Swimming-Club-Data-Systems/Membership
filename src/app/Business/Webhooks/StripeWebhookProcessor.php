<?php

namespace App\Business\Webhooks;

use App\Enums\Queue;
use Exception;
use Spatie\StripeWebhooks\Exceptions\WebhookFailed;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookProcessor;

class StripeWebhookProcessor extends WebhookProcessor
{
    protected function processWebhook(WebhookCall $webhookCall): void
    {
        try {
            if (! isset($webhookCall->payload['type']) || $webhookCall->payload['type'] === '') {
                throw WebhookFailed::missingType($webhookCall);
            }

            // Return if the event does not come from source which is the same live/sandbox mode
            if ($webhookCall->payload['livemode'] != config('stripe.livemode')) {
                return;
            }

            event("stripe-webhooks::{$webhookCall->payload['type']}", $webhookCall);

            $jobClass = $this->determineJobClass($webhookCall->payload['type']);

            if ($jobClass === '') {
                return;
            }

            if (! class_exists($jobClass)) {
                throw WebhookFailed::jobClassDoesNotExist($jobClass, $webhookCall);
            }

            if (! $webhookCall->exists()) {
                // Model is not persisted to db
                report('WebhookCall: '.$webhookCall->id.' is not persisted to db');
            }

            dispatch(new $jobClass($webhookCall))->onQueue(Queue::STRIPE->value);

            $webhookCall->clearException();

        } catch (Exception $exception) {
            $webhookCall->saveException($exception);

            throw $exception;
        }
    }

    protected function determineJobClass(string $eventType): string
    {
        $jobConfigKey = str_replace('.', '_', $eventType);

        $defaultJob = config('stripe-webhooks.default_job', '');

        return config("stripe-webhooks.jobs.{$jobConfigKey}", $defaultJob);
    }
}
