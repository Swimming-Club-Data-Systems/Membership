<?php

$db = DB::connection()->getPdo();
$user = app()->user;
$tenant = tenant()->getLegacyTenant();

$stripe = new \Stripe\StripeClient(getenv('STRIPE'));

$session = $stripe->billingPortal->sessions->create([
  'customer' => $tenant->getStripeCustomer(),
  'return_url' => autoUrl('admin'),
  'locale' => 'en-GB',
]);

http_response_code(302);
header("location: " . $session->url);