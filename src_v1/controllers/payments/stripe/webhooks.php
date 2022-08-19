<?php

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

function stripe_handlePayout($payout) {
  // Test if updating or new
  $db = app()->db;
  $getCount = $db->prepare("SELECT COUNT(*) FROM stripePayouts WHERE ID = ?");
  $getCount->execute([
    $payout->id
  ]);

  $amount = $payout->amount;
  if (($payout->status == 'canceled' || $payout->status == 'failed') || (isset($payout->failure_code) && $payout->failure_code != null)) {
    $amount = 0;
  }
  $date = new DateTime('@' . $payout->arrival_date, new DateTimeZone('UTC'));

  if ($getCount->fetchColumn() > 0) {
    // Update payout item
    $update = $db->prepare("UPDATE stripePayouts SET `Amount` = ?, `ArrivalDate` = ? WHERE `ID` = ?");
    $update->execute([
      $amount,
      $date->format("Y-m-d"),
      $payout->id
    ]);
  } else {
    // Add payout item
    try {
      $update = $db->prepare("INSERT INTO stripePayouts (ID, Amount, ArrivalDate) VALUES (?, ?, ?)");
      $update->execute([
        $payout->id,
        $amount,
        $date->format("Y-m-d")
      ]);
    } catch (Exception $e) {
      // 
    }
  }
}

function stripe_handlePayment($pi) {
  $db = app()->db;

  // Check if there are gala entries for this payment intent
  $getIntentDbId = $db->prepare("SELECT ID FROM stripePayments WHERE Intent = ?");
  $getIntentDbId->execute([
    $pi->id
  ]);
  $databaseId = $getIntentDbId->fetchColumn();
  if ($databaseId == null) {
    return;
  }

  $getGalaCount = $db->prepare("SELECT COUNT(*) FROM galaEntries WHERE StripePayment = ?");
  $getGalaCount->execute([
    $databaseId
  ]);
  if ($getGalaCount->fetchColumn() > 0) {
    // This payment was for galas so run the code for a successful gala payment
    handleCompletedGalaPayments($pi->id);
  } else {
    // Run code for any other type of payment
    // Such types do not exist yet but this is passive provision
  }

}

function stripe_handleNewPaymentIntent($intent) {
  $db = app()->db;

  $intentCreatedAt = new DateTime('@' . $intent->created, new DateTimeZone('UTC'));

  // Check if intent already exists
  $checkIntentCount = $db->prepare("SELECT COUNT(*) FROM stripePayments WHERE Intent = ?");
  $checkIntentCount->execute([
    $intent->id
  ]);

  $databaseId = null;
  if ($checkIntentCount->fetchColumn() == 0) {
    // Get the customer
    // Payments with no customer will be ignored
    $getCustomer = $db->prepare("SELECT `User` FROM stripeCustomers WHERE CustomerID = ?");
    $getCustomer->execute([$intent->customer]);
    $user = $getCustomer->fetchColumn();

    if ($user != null) {

      $refunded = 0;
      if (isset($intent->charges->data[0]->refunds->data)) {
        $refunds = $intent->charges->data[0]->refunds->data;
        foreach ($refunds as $refund) {
          $refunded += (int) $refund->amount;
        }
      }

      // Add this payment intent to the database and assign the id to each entry
      $addIntent = $db->prepare("INSERT INTO stripePayments (`User`, `DateTime`, Method, Intent, Amount, Currency, Paid, AmountRefunded) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $addIntent->execute([
        $user,
        $intentCreatedAt->format("Y-m-d H:i:s"),
        null,
        $intent->id,
        $intent->amount,
        $intent->currency,
        0,
        $refunded
      ]);

      $databaseId = $db->lastInsertId();

    }
  } else {
    $getIntentDbId = $db->prepare("SELECT ID FROM stripePayments WHERE Intent = ?");
    $getIntentDbId->execute([
      $intent->id
    ]);
    $databaseId = $getIntentDbId->fetchColumn();
  }
  $paymentDatabaseId = $databaseId;
}

/**
 * Disables a detached payment method, preventing future use, which would fail
 *
 * @param PaymentMethod $pm
 * @return void
 */
function stripe_detachPaymentMethod($pm) {
  $db = app()->db;
  $update = $db->prepare("UPDATE stripePayMethods SET Reusable = ? WHERE MethodID = ?");
  $update->execute([
    0,
    $pm->id
  ]);
}

function stripe_handlePaymentMethodUpdate($pm) {
  $db = app()->db;

  try {
    $id = $pm->id;
    $city = $pm->billing_details->address->city;
    $country = $pm->billing_details->address->country;
    $line1 = $pm->billing_details->address->line1;
    $line2 = $pm->billing_details->address->line2;
    $postal_code = $pm->billing_details->address->postal_code;
    $expMonth = $pm->card->exp_month;
    $expYear = $pm->card->exp_year;
    $last4 = $pm->card->last4;
    $threeDSecure = $pm->card->three_d_secure_usage->supported;

    $update = $db->prepare("UPDATE stripePayMethods SET City = ?, Country = ?, Line1 = ?, Line2 = ?, PostCode = ?, ExpMonth = ?, ExpYear = ?, Last4 = ? WHERE MethodID = ?");
    $update->execute([
      $city,
      $country,
      $line1,
      $line2,
      $postal_code,
      $expMonth,
      $expYear,
      $last4,
      $id
    ]);
    echo "Success";
  } catch (Exception $e) {
    
  }
}

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
  if (getenv('STRIPE_ENDPOINT_SECRET')) {
    $event = \Stripe\Webhook::constructEvent(
      $payload, $sig_header, $endpoint_secret
    );
  } else {
    $event = \Stripe\Event::constructFrom(
      json_decode($payload, true)
    );
  }
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  reportError($e);
  http_response_code(400);
  exit();
} catch(\Stripe\Error\SignatureVerification $e) {
  // Invalid signature
  reportError($e);
  http_response_code(400);
  exit();
}

// Handle the event
switch ($event->type) {
  case 'payment_method.card_automatically_updated':
    $paymentMethod = $event->data->object; // contains a \Stripe\PaymentIntent
    stripe_handlePaymentMethodUpdate($paymentMethod);
    break;
  case 'payment_method.updated':
    $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
    stripe_handlePaymentMethodUpdate($paymentMethod);
    break;
  case 'payment_intent.succeeded':
    $paymentIntent = $event->data->object;
    stripe_handlePayment($paymentIntent);
    break;
  case 'payment_intent.created':
    $paymentIntent = $event->data->object;
    stripe_handleNewPaymentIntent($paymentIntent);
    break;
  case 'payment_method.detached':
    $paymentMethod = $event->data->object;
    stripe_detachPaymentMethod($paymentMethod);
    break;
  case 'payout.canceled':
  case 'payout.created':
  case 'payout.failed':
  case 'payout.paid':
  case 'payout.updated':
    $payout = $event->data->object;
    stripe_handlePayout($payout);
    break;
  default:
    // Unexpected event type
    reportError(['Unexpected event type' => $event->type]);
    http_response_code(400);
    exit();
}

http_response_code(200);
