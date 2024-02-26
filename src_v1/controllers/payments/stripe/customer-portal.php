<?php

$db = app()->db;
$user = app()->user;
$tenant = app()->tenant;

$stripe = new \Stripe\StripeClient(getenv('STRIPE'));

try {
  $session = $stripe->billingPortal->sessions->create([
    'customer' => $user->getStripeCustomer(),
    'return_url' => autoUrl('payments'),
    'locale' => 'en-GB',
  ], [
    'stripe_account' => $tenant->getStripeAccount(),
  ]);

  http_response_code(302);
  header("location: " . $session->url);
} catch (Exception) {
  halt(404);
}
