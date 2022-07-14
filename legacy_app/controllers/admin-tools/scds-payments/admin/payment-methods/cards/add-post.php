<?php

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

// UPDATING
$customer = $tenant->getStripeCustomer();

$setupIntent = null;
if (!isset($_SESSION['StripeSetupIntentId'])) {

  http_response_code(302);
  header('location: ' . autoUrl('payments-admin/payment-cards/add'));
} else {
  try {
    $setupIntent = \Stripe\SetupIntent::retrieve(
      $_SESSION['StripeSetupIntentId']
    );

    if ($setupIntent->status == 'succeeded') {
      // 
      $_SESSION['StripeSetupIntentSuccess'] = true;

      http_response_code(302);
      header("Location: " . autoUrl("payments-admin/payment-cards/setup-success"));
    } else {

      $error = 'We could not set up your new payment method';

      if (isset($setupIntent->last_setup_error->message)) $error = $setupIntent->last_setup_error->message;

      $_SESSION['StripeSetupIntentError'] = $error;

      http_response_code(302);
      header("Location: " . autoUrl("payments-admin/payment-cards/add"));
    }
  } catch (Exception $e) {

    unset($_SESSION['StripeSetupIntentId']);
    $error = 'We could not set up your new payment method';
    $_SESSION['StripeSetupIntentError'] = $error;

    http_response_code(302);
    header("Location: " . autoUrl("payments-admin/payment-cards/add"));
    return;
  }
}
