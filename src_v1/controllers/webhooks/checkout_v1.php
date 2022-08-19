<?php

$tenant = app()->tenant;
$db = app()->db;

header("content-type: application/json");

$db->beginTransaction();

try {

  $data = json_decode(file_get_contents('php://input'));

  // Get org and payment
  if ($data->org != $tenant->getId()) {
    // 
    throw new Exception('Invalid org');
  }

  // Get payment_intent
  \Stripe\Stripe::setApiKey(getenv('STRIPE'));

  $intent = \Stripe\PaymentIntent::retrieve(
    [
      'id' => $data->payment,
      'expand' => ['customer', 'payment_method', 'charges.data.balance_transaction'],
    ],
    [
      'stripe_account' => $tenant->getStripeAccount(),
    ]
  );

  // Exception thrown if no intent

  // Check intent is checkout_v1 and then find checkout session
  if ($intent->metadata->payment_category != 'checkout_v1') throw new Exception('Not a Checkout_V1 Session');

  if ($intent->status != 'succeeded') throw new Exception('Unpaid');

  // Find checkout session
  $session = \SCDS\Checkout\Session::retrieve($intent->metadata->checkout_id);

  // Get payment method
  $getCount = $db->prepare("SELECT COUNT(*) FROM stripePayMethods WHERE MethodID = ?");
  $getCount->execute([
    $intent->payment_method->id,
  ]);

  $method = null;
  if ($getCount->fetchColumn() == 0) {
    // Add method to db

    // Get method details
    $method = \Stripe\PaymentMethod::retrieve(
      $intent->payment_method->id,
      [
        'stripe_account' => $tenant->getStripeAccount()
      ]
    );

    $reuse = true;

    // NOTE IN V1 WE ASSUME THE CUSTOMER ALREADY EXISTS IN STRIPE
    $getCardCount = $db->prepare("SELECT COUNT(*) FROM stripePayMethods WHERE Fingerprint = ? AND Customer = ? AND Reusable = '1'");
    $getCardCount->execute([
      $method->fingerprint,
      $intent->customer,
    ]);

    if ($getCardCount->fetchColumn() > 0) {
      $reuse = false;
    }

    // Attach payment method to customer iff it's to be reused
    // Also only if we can't see it in the DB for this user
    // Otherwise we're saving loads of non reusable Apple Pay cards etc.

    if ($reuse && (!$intent->customer || $method->customer == null)) {
      // $method->attach($method->customer); CHECK THIS
    } else if (!$method->customer || $method->customer == null) {
      $reuse = false;
    }

    $addMethod = $db->prepare("INSERT INTO stripePayMethods (Customer, MethodID, `Name`, CardName, City, Country, Line1, Line2, PostCode, Brand, IssueCountry, ExpMonth, ExpYear, Funding, Last4, Fingerprint, Reusable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $addMethod->execute([
      $method->customer,
      $method->id,
      'Unnamed card',
      $method->card->name,
      $method->billing_details->address->city,
      $method->billing_details->address->country,
      $method->billing_details->address->line1,
      $method->billing_details->address->line2,
      $method->billing_details->address->postal_code,
      $method->card->brand,
      $method->card->country,
      $method->card->exp_month,
      $method->card->exp_year,
      $method->card->funding,
      $method->card->last4,
      $method->card->fingerprint,
      $reuse,
    ]);

    $method = $db->lastInsertId();
  } else {
    // Find method ID

    $getMethod = $db->prepare("SELECT ID FROM stripePayMethods WHERE MethodID = ?");
    $getMethod->execute([
      $intent->payment_method->id,
    ]);
    $method = $getMethod->fetchColumn();
  }

  if (!$method) throw new Exception('Method missing');

  $now = new DateTime('now', new DateTimeZone('UTC'));

  // Update checkoutSession
  $updateSession = $db->prepare("UPDATE `checkoutSessions` SET `state` = ?, `succeeded` = ?, `method` = ? WHERE `id` = ?");
  $updateSession->execute([
    'succeeded',
    $now->format('Y-m-d H:i:s'),
    $intent->payment_method->id,
    $session->id,
  ]);

  // Add to stripePayments
  $insertPayment = $db->prepare("INSERT INTO `stripePayments` (`User`, `DateTime`, `Method`, `Intent`, `Amount`, `Currency`, `ServedBy`, `Paid`, `AmountRefunded`, `Fees`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $insertPayment->execute([
    $session->user,
    $now->format('Y-m-d H:i:s'),
    $method,
    $intent->id,
    $intent->amount,
    $intent->currency,
    null,
    true,
    0,
    $intent->charges->data[0]->balance_transaction->fee,
  ]);

  $paymentId = $db->lastInsertId();

  $checkoutItems = $session->getItems();

  // For each item, call item-specific stuff to handle payment status
  foreach ($checkoutItems as $item) {
    // Call method for item
    switch ($item->attributes->type) {
      case 'gala_entry':
        \SCDS\Checkout\ItemHandlers\GalaEntry::paid($item, $paymentId);
        break;
      case 'membership_batch_item':
        \SCDS\Checkout\ItemHandlers\MembershipFeeBatch::paid($item, $paymentId, $intent->id);
        break;
    }
  }

  $db->commit();

  // Send an email receipt to the user

  $markdown = new \ParsedownExtra();
  $markdown->setSafeMode(true);

  $html .= '<p>Thank you for making an online payment at ' . htmlspecialchars($tenant->getName()) . '. Here is your payment receipt.</p>';

  $html .= '<p>You paid for;</p>';

  $html .= '<table style="width: 100%; border: 1px solid #dddddd; border-collapse: collapse;"><thead><tr style="border: 1px solid #dddddd;"><th style="border: 1px solid #dddddd; padding: 5px;">Item</th><th style="border: 1px solid #dddddd; text-align: right; padding: 5px;">Price</th></tr></thead><tbody>';

  foreach ($checkoutItems as $item) {

    $html .= '<tr style="border: 1px solid #dddddd;"><td style="border: 1px solid #dddddd; padding: 5px;"><strong>' . htmlspecialchars($item->name) . '</strong>';
    if ($item->description) {
      $html .= $markdown->text($item->description);
    }

    if (sizeof($item->subItems) > 0) {
      $html .= '<ul>';
      foreach ($item->subItems as $subItem) {
        $html .= '<li>' . htmlspecialchars($subItem->name) . ', ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($subItem->amount), $subItem->currency)) . '</li>';
      }
      $html .= '</ul>';
    }

    $html .= '</td><td style="border: 1px solid #dddddd; text-align: right; padding: 5px;">' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->amount), $item->currency)) . '</td></tr>';
  }

  $html .= '<tr style="border: 1px solid #dddddd;"><td style="border: 1px solid #dddddd; padding: 5px;"><strong>Total</strong></td><td style="border: 1px solid #dddddd; text-align: right; padding: 5px;">' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($intent->amount), $intent->currency)) . '</td></tr>';

  $html .= '</tbody></table>';

  $html .= '<p><strong>Payment ID</strong> <br>SPM' . htmlspecialchars($paymentId) . '</p>';

  if ($intent->charges->data[0]->payment_method_details->card) {
    $html .= '<p><strong>Card</strong> <br>' . getCardBrand($intent->charges->data[0]->payment_method_details->card->brand) . ' ' . htmlspecialchars($intent->charges->data[0]->payment_method_details->card->funding) . ' card <br>&middot;&middot;&middot;&middot; ' . htmlspecialchars($intent->charges->data[0]->payment_method_details->card->last4) . '</p>';

    if ($intent->charges->data[0]->payment_method_details->card->wallet) {
      $html .= '<p><strong>Mobile wallet</strong> <br>' . getWalletName($intent->charges->data[0]->payment_method_details->card->wallet->type) . '</p>';

      if ($intent->charges->data[0]->payment_method_details->card->wallet->dynamic_last4) {
        $html .= '<p><strong>Device account number</strong> <br>&middot;&middot;&middot;&middot; ' . htmlspecialchars($intent->charges->data[0]->payment_method_details->card->wallet->dynamic_last4) . '</p>';
      }
    }
  }

  $html .= '<p><strong>SCDS Checkout reference</strong> <br>' . htmlspecialchars($session->id) . '</p>';

  if ($intent->charges->data[0]->billing_details->address) {
    $billingAddress = $intent->charges->data[0]->billing_details->address;

    $html .= '<p style="margin-bottom:0px;"><strong>Billing address</strong></p>';
    $html .= '<address>';

    if ($billingAddress->name) {
      $html .= htmlspecialchars($billingAddress->name) . '<br>';
    }

    if ($billingAddress->line1) {
      $html .= htmlspecialchars($billingAddress->line1) . '<br>';
    }

    if ($billingAddress->line2) {
      $html .= htmlspecialchars($billingAddress->line2) . '<br>';
    }

    if ($billingAddress->postal_code) {
      $html .= htmlspecialchars($billingAddress->postal_code) . '<br>';
    }

    if ($billingAddress->state) {
      $html .= htmlspecialchars($billingAddress->state) . '<br>';
    }

    if ($billingAddress->country) {
      $html .= htmlspecialchars($billingAddress->country) . '<br>';
    }

    $html .= '</address>';
  }

  $html .= '<p><strong>General notes for payments</strong> <br>You can find more information about our payment terms and returns policy on our website. All payments are subject to scheme or network rules.</p>';
  $html .= '<p>Payment services are provided to ' . htmlspecialchars($tenant->getName()) . ' by SCDS and their payment processing partners. PCI DSS compliance is primarily handled by our payment processors.</p>';
  $html .= '<p>' . htmlspecialchars($tenant->getName()) . ' may sometimes place a temporary hold of 0GBP to 1GBP or 1USD on your card when you first add it to your account. This is part of the card authorisation process that allows us to determine that your card is valid. This charge will drop off your statement within a few days.</p>';

  if (isset($intent->charges->data[0]->payment_method_details->card->brand) && $intent->charges->data[0]->calculated_statement_descriptor && $intent->charges->data[0]->payment_method_details->card->brand == 'amex') {
    $html .= '<p>American Express customers may see <strong>Stripe</strong> in online banking and the Amex app while the payment is pending. This will usually update to <strong>' . htmlspecialchars($intent->charges->data[0]->calculated_statement_descriptor) . '</strong> within 48 hours or when the payment settles.</p>';
  }

  $html .= '<p><strong>Notes for gala entry payments</strong> <br>In accordance with card network rules, refunds for gala rejections will only be made to the payment card which was used.</p>';
  $html .= '<p>Should you wish to withdraw your swimmers you will need to contact the gala coordinator. Depending on the gala and host club, you may not be eligible for a refund unless you can provide evidence to back up the reason for withdrawal, such as a doctors note.</p>';

  $email = null;
  $name = null;

  // Get user details
  $user = new User($session->user);

  $email = $user->getEmail();
  $name = $user->getFullName();

  if ($intent->charges->data[0]->billing_details->email) {
    $email = $intent->charges->data[0]->billing_details->email;
    if ($intent->charges->data[0]->billing_details->name) {
      $name = $intent->charges->data[0]->billing_details->name;
    }
  }

  notifySend(null, 'Payment Receipt', $html, $name, $email);

  http_response_code(200);
  echo json_encode([
    'status' => 200,
  ]);
} catch (Exception $e) {

  $db->rollBack();

  reportError($e);

  http_response_code(500);
  echo json_encode([
    'status' => 500,
  ]);
}
