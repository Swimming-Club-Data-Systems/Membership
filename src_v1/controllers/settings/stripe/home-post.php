<?php

$tenant = app()->tenant;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
$at = app()->tenant->getStripeAccount();

$stripeAccount = \Stripe\Account::retrieve($at);
$supportsDirectDebit = isset($stripeAccount->capabilities->bacs_debit_payments) && $stripeAccount->capabilities->bacs_debit_payments == 'active';

if ($tenant->getStripeAccount()) {
// USE_STRIPE_DIRECT_DEBIT
  if (isset($_POST['GALA_CARD_PAYMENTS_ALLOWED'])) {
    $tenant->setKey('GALA_CARD_PAYMENTS_ALLOWED', (int) bool($_POST['GALA_CARD_PAYMENTS_ALLOWED']));
  } else {
    $tenant->setKey('GALA_CARD_PAYMENTS_ALLOWED', (int) false);
  }

  if (isset($_POST['ALLOW_DIRECT_DEBIT_OPT_OUT'])) {
    $tenant->setKey('ALLOW_DIRECT_DEBIT_OPT_OUT', (int) bool($_POST['ALLOW_DIRECT_DEBIT_OPT_OUT']));
  } else {
    $tenant->setKey('ALLOW_DIRECT_DEBIT_OPT_OUT', (int) false);
  }

  if (!$tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT') && $supportsDirectDebit) {

    if (isset($_POST['ALLOW_STRIPE_DIRECT_DEBIT_SET_UP'])) {
      $tenant->setKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP', (int) bool($_POST['ALLOW_STRIPE_DIRECT_DEBIT_SET_UP']));
    } else {
      $tenant->setKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP', (int) false);
    }

    if (isset($_POST['USE_STRIPE_DIRECT_DEBIT'])) {
      $tenant->setKey('USE_STRIPE_DIRECT_DEBIT', (int) bool($_POST['USE_STRIPE_DIRECT_DEBIT']));
    }
  }

  // pre(bool($_POST['GALA_CARD_PAYMENTS_ALLOWED']));

}

header("location: " . autoUrl('settings/stripe'));