<?php

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

try {

  $session = \Stripe\Checkout\Session::retrieve([
    'id' => $_GET['session_id'],
    'expand' => ['setup_intent'],
  ], [
    'stripe_account' => app()->tenant->getStripeAccount()
  ]);
  $intent = $session->setup_intent;

  if ($intent->status != 'succeeded') {
    throw new Exception('SetupIntent has not succeeded!');
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['StripeDDSuccess'] = true;
  if (isset($renewal_trap) && $renewal_trap) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['RegRenewalDDSuccess'] = true;
    header("location: " . autoUrl("renewal/go"));
  } else {
    header("location: " . autoUrl("payments/direct-debit"));
  }

} catch (Exception $e) {

  $_SESSION['TENANT-' . app()->tenant->getId()]['StripeDDError'] = true;
  if (isset($renewal_trap) && $renewal_trap) {
    header("location: " . autoUrl("renewal/payments/direct-debit/set-up"));
  } else {
    header("location: " . autoUrl("payments/direct-debit/set-up"));
  }

}