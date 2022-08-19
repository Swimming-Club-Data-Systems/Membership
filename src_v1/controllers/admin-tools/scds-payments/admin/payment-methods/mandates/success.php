<?php

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

try {

  $session = \Stripe\Checkout\Session::retrieve([
    'id' => $_GET['session_id'],
    'expand' => ['setup_intent'],
  ]);
  $intent = $session->setup_intent;

  if ($intent->status != 'succeeded') {
    throw new Exception('SetupIntent has not succeeded!');
  }

  $_SESSION['StripeDDSuccess'] = true;
  header("location: " . autoUrl("payments-admin/direct-debit-instruction"));

} catch (Exception $e) {

  $_SESSION['StripeDDError'] = true;
  header("location: " . autoUrl("payments-admin/direct-debit-instruction/set-up"));

}