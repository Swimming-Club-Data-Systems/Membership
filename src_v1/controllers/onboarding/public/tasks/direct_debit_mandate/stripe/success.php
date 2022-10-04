<?php

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(503);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

if (!app()->tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') || !getenv('STRIPE')) halt(403);

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

$checkoutSession = \Stripe\Checkout\Session::retrieve([
  'id' => $_GET['session_id'],
  'expand' => ['setup_intent', 'setup_intent.payment_method'],
], [
  'stripe_account' => $tenant->getStripeAccount()
]);
$intent = $checkoutSession->setup_intent;

http_response_code(303);

if ($intent->status == 'succeeded') {
  $session->completeTask('direct_debit_mandate');

  $_SESSION['SetupMandateSuccess'] = [
    'SortCode' => $intent->payment_method->bacs_debit->sort_code,
    'Last4' => '····' . $intent->payment_method->bacs_debit->last4,
  ];

  header('location: ' . autoUrl('onboarding/go'));
} else {
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
