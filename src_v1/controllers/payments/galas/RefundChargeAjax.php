<?php

/**
 * Code for handling refunds server side
 */

$status = 'pending';
$reason = null;

// Extract data from POST request
$entry = (int) $_POST['entry'];
$amountRefunded = (int) $_POST['amountRefunded'];
$refundAmount = (int) $_POST['refundAmount'];

$requestData = [
  'entry' => $entry,
  'amountRefunded' => $amountRefunded,
  'refundAmount' => $refundAmount
];

$db = app()->db;
$tenant = app()->tenant;

$getMandates = $db->prepare("SELECT ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates WHERE Customer = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1");

$swimsArray = [
  '25Free' => '25&nbsp;Free',
  '50Free' => '50&nbsp;Free',
  '100Free' => '100&nbsp;Free',
  '200Free' => '200&nbsp;Free',
  '400Free' => '400&nbsp;Free',
  '800Free' => '800&nbsp;Free',
  '1500Free' => '1500&nbsp;Free',
  '25Back' => '25&nbsp;Back',
  '50Back' => '50&nbsp;Back',
  '100Back' => '100&nbsp;Back',
  '200Back' => '200&nbsp;Back',
  '25Breast' => '25&nbsp;Breast',
  '50Breast' => '50&nbsp;Breast',
  '100Breast' => '100&nbsp;Breast',
  '200Breast' => '200&nbsp;Breast',
  '25Fly' => '25&nbsp;Fly',
  '50Fly' => '50&nbsp;Fly',
  '100Fly' => '100&nbsp;Fly',
  '200Fly' => '200&nbsp;Fly',
  '100IM' => '100&nbsp;IM',
  '150IM' => '150&nbsp;IM',
  '200IM' => '200&nbsp;IM',
  '400IM' => '400&nbsp;IM'
];

try {

  $date = date("Y-m-d");
  $insertPayment = $db->prepare("INSERT INTO paymentsPending (`Date`, `Status`, UserID, `Name`, Amount, Currency, PMkey, `Type`, MetadataJSON) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $markAsRefunded = $db->prepare("UPDATE galaEntries SET Refunded = ?, AmountRefunded = ? WHERE EntryID = ?");
  $notify = $db->prepare("INSERT INTO notify (UserID, `Status`, `Subject`, `Message`, EmailType) VALUES (?, ?, ?, ?, ?)");
  $setRefundAmount = $db->prepare("UPDATE stripePayments SET `AmountRefunded` = ? WHERE `Intent` = ?");

  $getGala = $db->prepare("SELECT GalaName `name`, GalaFee fee, GalaVenue venue, GalaFeeConstant fixed FROM galas INNER JOIN galaEntries ON galas.GalaID = galaEntries.GalaID WHERE EntryID = ? AND galas.Tenant = ?");
  $getGala->execute([
    $entry,
    $tenant->getId()
  ]);
  $gala = $getGala->fetch(PDO::FETCH_ASSOC);

  if ($gala == null) {
    throw new Exception('We could not find a gala with the provided id number');
  }

  $getEntry = $db->prepare("SELECT members.UserID `user`, 25Free, 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 25Back, 50Back, 100Back, 200Back, 25Breast, 50Breast, 100Breast, 200Breast, 25Fly, 50Fly, 100Fly, 200Fly, 100IM, 150IM, 200IM, 400IM, MForename, MSurname, EntryID, Charged, FeeToPay, MandateID, EntryProcessed Processed, Refunded, galaEntries.AmountRefunded, Intent, users.UserID, stripePayments.Paid StripePaid FROM (((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN users ON members.UserID = users.UserID) LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) LEFT JOIN stripePayments ON galaEntries.StripePayment = stripePayments.ID) WHERE galaEntries.EntryID = ? AND Charged = ? AND EntryProcessed = ?");
  $getEntry->execute([$entry, '1', '1']);

  $entryData = $getEntry->fetch(PDO::FETCH_ASSOC);

  if ($entryData == null) {
    throw new Exception('We could not find an entry with the provided id number');
  }

  if ($amountRefunded != $entryData['AmountRefunded']) {
    throw new Exception('A verification check fail. Please refresh the page to refund this entry');
  }

  $toPay = (int) (\Brick\Math\BigDecimal::of((string) $entryData['FeeToPay']))->withPointMovedRight(2)->toInt();
  $amountRefundable = $toPay - ($entryData['AmountRefunded']);

  if ($refundAmount < 0 || $refundAmount > $amountRefundable) {
    throw new Exception('The amount you\'re attempting to refund is not allowed');
  }

  $hasNoGCDD = ($entryData['MandateID'] == null) || (getUserOption($entryData['user'], 'GalaDirectDebitOptOut'));
  $stripeCusomer = null;
  if ($entryData['user']) {
    $stripeCusomer = (new User($entryData['user']))->getStripeCustomer();
  }
  if ($stripeCusomer) {
    $getMandates->execute([
      $stripeCusomer->id,
    ]);
  }
  $mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

  $hasNoSDD = !$mandate || (getUserOption($entryData['user'], 'GalaDirectDebitOptOut'));

  $hasNoDD = ($hasNoSDD && $tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) || ($hasNoGCDD && !$tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT'));

  $swimsList = '<ul>';
  foreach ($swimsArray as $colTitle => $text) {
    if ($entryData[$colTitle]) {
      $swimsList .= '<li>' . $text . '</li>';
    }
  }
  $swimsList .= '</ul>';

  $db->beginTransaction();

  $refundAm = (\Brick\Math\BigDecimal::of((string) $refundAmount))->withPointMovedLeft(2);
  $amountString = (string) (\Brick\Math\BigDecimal::of((string) $refundAmount))->withPointMovedLeft(2)->toScale(2);

  $prevRef = (\Brick\Math\BigDecimal::of((string) $entryData['AmountRefunded']))->withPointMovedLeft(2);
  $refundTotal = $refundAm->plus($prevRef);
  $totalString = (string) $refundTotal->toScale(2);

  if (!$hasNoDD && ($entryData['Intent'] == null || !bool($entryData['StripePaid']))) {

    // Refund via direct debit bills
    $name = 'REJECTIONS REFUND ' . $entryData['MForename'] . ' ' . $entryData['MSurname'] . '\'s Gala Entry into ' . $gala['name'] .  ' (Entry #' . $entryData['EntryID'] . ')';

    $jsonArray = [
      "Name" => $name,
      "type" => [
        "object" => 'GalaEntry',
        "id" => $id,
        "name" => $gala['name']
      ]
    ];
    $json = json_encode($jsonArray);

    $insertPayment->execute([
      $date,
      'Pending',
      $entryData['UserID'],
      'Gala Entry (#' . $entryData['EntryID'] . ')',
      $refundAmount,
      'GBP',
      null,
      'Refund',
      $json
    ]);
  } else if ($entryData['Intent'] != null && bool($entryData['StripePaid']) && getenv('STRIPE') && $tenant->getStripeAccount()) {
    // Refund to card used

    try {
      \Stripe\Stripe::setApiKey(getenv('STRIPE'));
      $intent = \Stripe\PaymentIntent::retrieve(
        $entryData['Intent'],
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
    } catch (\Stripe\Exception\CardException $e) {
      // Since it's a decline, \Stripe\Exception\CardException will be caught
      throw new Exception($e->getStripeError()->message);
    } catch (\Stripe\Exception\RateLimitException $e) {
      // Too many requests made to the API too quickly
      throw new Exception($e->getStripeError()->message);
    } catch (\Stripe\Exception\InvalidRequestException $e) {
      // Invalid parameters were supplied to Stripe's API
      throw new Exception($e->getStripeError()->message);
    } catch (\Stripe\Exception\AuthenticationException $e) {
      // Authentication with Stripe's API failed
      // (maybe you changed API keys recently)
      throw new Exception($e->getStripeError()->message);
    } catch (\Stripe\Exception\ApiConnectionException $e) {
      // Network communication with Stripe failed
      throw new Exception($e->getStripeError()->message);
    } catch (\Stripe\Exception\ApiErrorException $e) {
      // Display a very generic error to the user, and maybe send
      // yourself an email
      throw new Exception($e->getStripeError()->message);
    } catch (Exception $e) {
      // Something else happened, completely unrelated to Stripe
      $report = '';
      if (reportError($e)) {
        $report = '. We have attemted to report the error to your system administrator';
      }
      throw new Exception('An unknown error occurred' . $report);
    }

    try {
      \Stripe\Stripe::setApiKey(getenv('STRIPE'));
      $intent = \Stripe\PaymentIntent::retrieve(
        $entryData['Intent'],
        [
          'stripe_account' => $tenant->getStripeAccount()
        ]
      );

      // Update amount refunded on payment
      if (isset($intent->charges->data[0]->amount_refunded)) {
        $setRefundAmount->execute([
          $intent->charges->data[0]->amount_refunded,
          $intent->id,
        ]);
      }
    } catch (Exception $e) {
      // This is not a showstopping error
    }
  }

  $markAsRefunded->execute([
    true,
    $refundAmount + $entryData['AmountRefunded'],
    $entryData['EntryID']
  ]);

  if ($entryData['user']) {

    $message = '<p>We\'ve issued a refund for ' . htmlspecialchars($entryData['MForename']) .  '\'s entry into ' . htmlspecialchars($gala['name']) . '.</p>';

    $message .= '<p>This refund is to the value of <strong>&pound;' . $amountString . '</strong>.</p>';

    if ($refundAmount + $entryData['AmountRefunded'] > $entryData['AmountRefunded']) {
      $message .= '<p>Please note that this brings the total amount refunded for this gala to &pound;' . $totalString . '</p>';
    }

    if (!$hasNoDD && !getUserOption($entryData['user'], 'GalaDirectDebitOptOut') && ($entryData['Intent'] == null || !bool($entryData['StripePaid']))) {
      $message .= '<p>This refund has been applied as a credit to your club account. This means you will either;</p>';
      $message .= '<ul><li>If you have not paid the bill by direct debit for this gala yet, you will automatically be charged the correct amount for ' . htmlspecialchars($gala['name']) . ' on your next bill as refunds will be applied automatically</li><li>If you have already paid the bill by direct debit for this gala, the credit applied to your account will give you a discount on next month\'s bill</li></ul>';
    } else if ($entryData['Intent'] != null && bool($entryData['StripePaid'])) {
      $message .= '<p>We\'ve refunded this payment to your original payment card.</p>';
    } else {
      $message .= '<p>As you don\'t pay your club fees by direct debit or have opted out of paying for galas by direct debit, you\'ll need to collect this refund from the treasurer or gala coordinator.</p>';
    }

    $message .= '<p>Kind regards,<br> The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';

    $notify->execute([

      $entryData['user'],
      'Queued',
      'Refund for Rejections: ' . $entryData['MForename'] .  '\'s ' . $gala['name'] . ' Entry',
      $message,
      'Galas'
    ]);
  }

  $db->commit();

  // Everything successful

  $getAmounts = $db->prepare("SELECT  FeeToPay, AmountRefunded FROM galaEntries WHERE EntryID = ?");
  $getAmounts->execute([$entry]);
  $amounts = $getAmounts->fetch(PDO::FETCH_ASSOC);

  $reponse = [
    'status' => 'success',
    'request_data' => $requestData,
    'amount_refundable' => (int) (\Brick\Math\BigDecimal::of((string) $amounts['FeeToPay']))->withPointMovedRight(2)->toInt() - $amounts['AmountRefunded'],
    'amount_refunded' => (int) $amounts['AmountRefunded']
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
