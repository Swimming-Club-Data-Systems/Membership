<?php

$db = app()->db;
$user = app()->user;
$tenant = app()->tenant;

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

$successUrl = autoUrl('admin/scds-payments/setup-direct-debit/success?session_id={CHECKOUT_SESSION_ID}');
$cancelUrl = autoUrl('admin');

$session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['bacs_debit'],
  'mode' => 'setup',
  'customer' => $tenant->getStripeCustomer(),
  'success_url' => $successUrl,
  'cancel_url' => $cancelUrl,
  'locale' => 'en-GB',
  'metadata' => [
    'session_type' => 'direct_debit_setup',
  ],
]);

http_response_code(303);
header('location: ' . $session->url);