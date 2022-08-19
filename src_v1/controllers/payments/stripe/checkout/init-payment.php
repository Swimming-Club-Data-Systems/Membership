<?php

$db = app()->db;

function generatePaymentResponse($intent) {
  if ($intent->status == 'requires_source_action' &&
    $intent->next_action->type == 'use_stripe_sdk') {
    # Tell the client to handle the action
    echo json_encode([
        'requires_action' => true,
        'payment_intent_client_secret' => $intent->client_secret
    ]);
  } else if ($intent->status == 'succeeded') {
    # The payment didn’t need any additional actions and completed!
    # Handle post-payment fulfillment
    echo json_encode([
        'success' => true
    ]);
  } else {
    # Invalid status
    http_response_code(500);
    echo json_encode(['error' => 'Invalid PaymentIntent status']);
  }
}

// See your keys here: https://dashboard.stripe.com/account/apikeys
\Stripe\Stripe::setApiKey(getenv('STRIPE'));

$getCustID = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
$getCustID->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
$customer = \Stripe\Customer::retrieve($getCustID->fetchColumn());

$intent = \Stripe\PaymentIntent::create([
  'amount' => 1099,
  'payment_method' => '{{PAYMENT_METHOD_ID}}',
  'currency' => 'gbp',
  'confirmation_method' => 'manual',
  'confirm' => true,
  'customer' => $customer->id
]);

generatePaymentResponse($intent);