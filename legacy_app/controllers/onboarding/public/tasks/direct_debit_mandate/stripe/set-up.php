<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(503);

$user = $session->getUser();

$tenant = tenant()->getLegacyTenant();

$logos = config('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

if (!config('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') || !getenv('STRIPE')) halt(403);

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
// UPDATING
$customer = $user->getStripeCustomer();

$successUrl = autoUrl('onboarding/go/direct-debit/stripe/success?session_id={CHECKOUT_SESSION_ID}');
$cancelUrl = autoUrl('onboarding/go/start-task');

$session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['bacs_debit'],
  'mode' => 'setup',
  'customer' => $customer->id,
  'success_url' => $successUrl,
  'cancel_url' => $cancelUrl,
  'locale' => 'en-GB',
  'metadata' => [
    'session_type' => 'direct_debit_setup',
  ],
], [
  'stripe_account' => $tenant->getStripeAccount()
]);

http_response_code(303);
header('location: ' . $session->url);
