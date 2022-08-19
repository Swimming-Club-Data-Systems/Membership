<?php

/**
 * Check we know;s
 * - Item ID
 * - That this is not a gala payment
 * - User is authorised /
 */

$user = app()->user;
$db = app()->db;
$tenant = app()->tenant;

// reportError($_POST);

if (!$user->hasPermission('Admin')) halt(404);

if (!\SCDS\CSRF::verify()) halt(403);

$status = 'pending';
$reason = null;

// Extract data from POST request
$itemId = (int) $_POST['item'];
$amountRefunded = (int) $_POST['amount-refunded'];
$refundAmount = (int) $_POST['refund-amount'];

$requestData = [
  'item' => $itemId,
  'amountRefunded' => $amountRefunded,
  'refundAmount' => $refundAmount
];

// Get item and payment
$getItem = $db->prepare("SELECT stripePaymentItems.ID itemId, stripePaymentItems.Amount amount, stripePaymentItems.AmountRefunded refunded, stripePaymentItems.Name itemName, stripePaymentItems.Description `description`, stripePayments.Intent intent, stripePayments.ID paymentId, stripePayments.Amount totalAmount, stripePayments.AmountRefunded totalRefunded, stripePayments.Currency currency, users.Forename fn, users.Surname sn, users.EmailAddress email, users.UserID `user`, stripePayMethods.Brand brand, stripePayMethods.Funding funding, stripePayMethods.Last4 last4 FROM stripePaymentItems INNER JOIN stripePayments ON stripePaymentItems.Payment = stripePayments.ID INNER JOIN users ON stripePayments.User = users.UserID INNER JOIN stripePayMethods ON stripePayMethods.ID = stripePayments.Method WHERE stripePaymentItems.ID = ? AND users.Tenant = ?");
$getItem->execute([
  $_POST['item'],
  $tenant->getId(),
]);

$item = $getItem->fetch(PDO::FETCH_OBJ);

try {

  $getGalaEntries = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN galas ON galas.GalaID = galaEntries.GalaID) INNER JOIN members ON members.MemberID = galaEntries.MemberID) WHERE StripePayment = ?");
  $getGalaEntries->execute([
    $item->paymentId,
  ]);
  $ents = $getGalaEntries->fetch(PDO::FETCH_ASSOC);

  if ($ents) throw new Exception('You can not refund gala payments here');

  $db->beginTransaction();

  if (!$item) {
    throw new Exception('We could not find a payment item with the provided id number');
  }

  if ($amountRefunded != $item->refunded) {
    throw new Exception('A verification check fail. Please refresh the page to refund this entry');
  }

  $toPay = (int) $item->amount;
  $amountRefundable = $toPay - ($item->refunded);

  if ($refundAmount < 0 || $refundAmount > $amountRefundable) {
    throw new Exception('The amount you\'re attempting to refund is not allowed');
  }

  \Stripe\Stripe::setApiKey(getenv('STRIPE'));
  $intent = \Stripe\PaymentIntent::retrieve(
    $item->intent,
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );
  $re = \Stripe\Refund::create(
    [
      "charge" => $intent->charges->data[0]->id,
      "amount" => $refundAmount
    ],
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );

  // Success if we get to here

  $intent = \Stripe\PaymentIntent::retrieve(
    $item->intent,
    [
      'stripe_account' => $tenant->getStripeAccount()
    ]
  );

  // Update amount refunded on payment
  if (isset($intent->charges->data[0]->amount_refunded)) {
    $setRefundAmount = $db->prepare("UPDATE stripePayments SET `AmountRefunded` = ? WHERE `Intent` = ?");

    $setRefundAmount->execute([
      $intent->charges->data[0]->amount_refunded,
      $intent->id,
    ]);
  }

  // Update amount refunded on payment item
  if (isset($intent->charges->data[0]->amount_refunded)) {
    $setRefundAmount = $db->prepare("UPDATE stripePayments SET `AmountRefunded` = ? WHERE `Intent` = ?");

    $setRefundAmount->execute([
      $intent->charges->data[0]->amount_refunded,
      $intent->id,
    ]);

    $setRefundAmount = $db->prepare("UPDATE stripePaymentItems SET `AmountRefunded` = ? WHERE `ID` = ?");

    $setRefundAmount->execute([
      $refundAmount + (int) $item->refunded,
      $itemId,
    ]);
  }

  // Update data
  $getItem->execute([
    $_POST['item'],
    $tenant->getId(),
  ]);

  $item = $getItem->fetch(PDO::FETCH_OBJ);

  if ($item->user) {

    $notify = $db->prepare("INSERT INTO notify (UserID, `Status`, `Subject`, `Message`, EmailType) VALUES (?, ?, ?, ?, ?)");

    $message = '<p>We\'ve issued a refund of <strong>' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($refundAmount), 'GBP')) . '</strong> for <em>' . htmlspecialchars($item->itemName) .  ', ' . htmlspecialchars($item->description) . '</em> to your ' . htmlspecialchars(getCardBrand($item->brand) . ' ' . $item->funding . ' card ending ' . $item->last4) . '.</p>';

    if ($item->refunded > 0) {
      $message .= '<p>This brings the total amount refunded for <em>' . htmlspecialchars($item->itemName) .  ', ' . htmlspecialchars($item->description) . '</em> to ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->refunded), 'GBP')) . ' and the total amount refunded for this card payment to ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->totalRefunded), 'GBP')) . '.</p>';
      $message .= '<p>Your adjusted total is ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($item->totalAmount - $item->totalRefunded), 'GBP')) . '.</p>';
    }

    $message .= '<p>It can take 10+ days to appear on your statement, if it takes longer please contact your bank for assistance.</p>';

    $message .= '<p><a href="' . htmlspecialchars(autoUrl('payments/card-transactions/' . $item->paymentId)) . '">View your up to date receipt online</a>.</p>';

    $message .= '<p>Kind regards,<br> The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';

    $notify->execute([

      $item->user,
      'Queued',
      'Card payment refund for ' . $item->itemName,
      $message,
      'Refunds'
    ]);
  }

  $db->commit();

  // header('content-type: application/json');
  // echo json_encode($item);

  $toPay = (int) $item->amount;
  $amountRefundable = ((int) $item->amount) - ((int) $item->refunded);

  $reponse = [
    'status' => 'success',
    'request_data' => $requestData,
    'amount_refundable' => $amountRefundable,
    'amount_refunded' => (int) $item->refunded,
  ];

  header('Content-Type: application/json');

  echo json_encode($reponse);
} catch (Exception $e) {
  // A problem occured
  if ($db->inTransaction()) {
    $db->rollBack();
  }

  $reponse = [
    'status' => 'error',
    'request_data' => $requestData,
    'error_message' => $e->getMessage()
  ];

  header('Content-Type: application/json');

  echo json_encode($reponse);
}
