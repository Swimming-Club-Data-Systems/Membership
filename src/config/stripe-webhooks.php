<?php

return [
    /*
     * Stripe will sign each webhook using a secret. You can find the used secret at the
     * webhook configuration settings: https://dashboard.stripe.com/account/webhooks.
     */
    'signing_secret' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),

    /*
     * You can define a default job that should be run for all other Stripe event type
     * without a job defined in next configuration.
     * You may leave it empty to store the job in database but without processing it.
     */
    'default_job' => '',

    /*
     * You can define the job that should be run when a certain webhook hits your application
     * here. The key is the name of the Stripe event type with the `.` replaced by a `_`.
     *
     * You can find a list of Stripe webhook types here:
     * https://stripe.com/docs/api#event_types.
     */
    'jobs' => [
        'checkout_session_completed' => \App\Jobs\StripeWebhooks\HandleCheckoutSessionCompleted::class,
        'payment_method_attached' => \App\Jobs\StripeWebhooks\HandlePaymentMethodAttached::class,
        'payment_method_detached' => \App\Jobs\StripeWebhooks\HandlePaymentMethodDetached::class,
        'payment_method_updated' => \App\Jobs\StripeWebhooks\HandlePaymentMethodUpdated::class,
        'payment_method_automatically_updated' => \App\Jobs\StripeWebhooks\HandlePaymentMethodAutomaticallyUpdated::class,
        'mandate_updated' => \App\Jobs\StripeWebhooks\HandleMandateUpdated::class,
        'payment_intent_canceled' => \App\Jobs\StripeWebhooks\HandlePaymentIntentCanceled::class,
        'payment_intent_created' => \App\Jobs\StripeWebhooks\HandlePaymentIntentCreated::class,
        'payment_intent_partially_funded' => \App\Jobs\StripeWebhooks\HandlePaymentIntentPartiallyFunded::class,
        'payment_intent_payment_failed' => \App\Jobs\StripeWebhooks\HandlePaymentIntentPaymentFailed::class,
        'payment_intent_processing' => \App\Jobs\StripeWebhooks\HandlePaymentIntentProcessing::class,
        'payment_intent_requires_action' => \App\Jobs\StripeWebhooks\HandlePaymentIntentRequiresAction::class,
        'payment_intent_succeeded' => \App\Jobs\StripeWebhooks\HandlePaymentIntentSucceeded::class,
        'charge_dispute_closed' => \App\Jobs\StripeWebhooks\HandleChargeDisputeClosed::class,
        'charge_dispute_created' => \App\Jobs\StripeWebhooks\HandleChargeDisputeClosed::class,
        'charge_dispute_updated' => \App\Jobs\StripeWebhooks\HandleChargeDisputeUpdated::class,
    ],

    /*
     * The classname of the model to be used. The class should equal or extend
     * Spatie\WebhookClient\Models\WebhookCall.
     */
    'model' => \Spatie\WebhookClient\Models\WebhookCall::class,

    /**
     * This class determines if the webhook call should be stored and processed.
     */
    'profile' => \Spatie\StripeWebhooks\StripeWebhookProfile::class,

    /*
     * Specify a connection and or a queue to process the webhooks
     */
    'queue' => env('STRIPE_WEBHOOK_QUEUE', 'stripe'),

    /*
     * When disabled, the package will not verify if the signature is valid.
     * This can be handy in local environments.
     */
    'verify_signature' => env('STRIPE_SIGNATURE_VERIFY', true),
];
