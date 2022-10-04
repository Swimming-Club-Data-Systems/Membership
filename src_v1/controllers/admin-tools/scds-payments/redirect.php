<?php

$db = app()->db;
$user = app()->user;
$tenant = app()->tenant;

$stripe = new \Stripe\StripeClient(getenv('STRIPE'));

$session = $stripe->billingPortal->sessions->create([
  'customer' => $tenant->getStripeCustomer(),
  'return_url' => autoUrl('admin'),
  'locale' => 'en-GB',
]);

http_response_code(302);
header("location: " . $session->url);