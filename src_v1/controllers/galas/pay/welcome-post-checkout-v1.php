<?php

\Stripe\Stripe::setApiKey(getenv('STRIPE'));
if (getenv('STRIPE_APPLE_PAY_DOMAIN')) {
  \Stripe\ApplePayDomain::create([
    'domain_name' => getenv('STRIPE_APPLE_PAY_DOMAIN')
  ]);
}

$db = app()->db;
$tenant = app()->tenant;

$updateTime = $db->prepare("UPDATE galaEntries SET FeeToPay = ? WHERE EntryID = ?");

$date = new DateTime('now', new DateTimeZone('Europe/London'));

$swimsArray = [
  '25Free' => '25 Free',
  '50Free' => '50 Free',
  '100Free' => '100 Free',
  '200Free' => '200 Free',
  '400Free' => '400 Free',
  '800Free' => '800 Free',
  '1500Free' => '1500 Free',
  '25Back' => '25 Back',
  '50Back' => '50 Back',
  '100Back' => '100 Back',
  '200Back' => '200 Back',
  '25Breast' => '25 Breast',
  '50Breast' => '50 Breast',
  '100Breast' => '100 Breast',
  '200Breast' => '200 Breast',
  '25Fly' => '25 Fly',
  '50Fly' => '50 Fly',
  '100Fly' => '100 Fly',
  '200Fly' => '200 Fly',
  '100IM' => '100 IM',
  '150IM' => '150 IM',
  '200IM' => '200 IM',
  '400IM' => '400 IM'
];

$rowArray = [1, null, null, null, null, null, 2, 1,  null, null, 2, 1, null, null, 2, 1, null, null, 2, 1, null, null, 2];
$rowArrayText = ["Freestyle", null, null, null, null, null, 2, "Backstroke",  null, null, 2, "Breaststroke", null, null, 2, "Butterfly", null, null, 2, "Individual Medley", null, null, 2];

try {
  $entries = $db->prepare("SELECT *, galaEntries.ProcessingFee pFee FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE members.UserID = ? AND (NOT RequiresApproval OR (RequiresApproval AND Approved)) AND NOT Charged AND FeeToPay > 0 AND galas.GalaDate >= ?");
  $entries->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $date->format("Y-m-d")]);
} catch (Exception $e) {
  pre($e);
}
$entry = $entries->fetch(PDO::FETCH_ASSOC);

//pre($entry);

http_response_code(302);

$payingEntries = [];

$checkoutSession = \SCDS\Checkout\Session::new([
  'user' => app()->user->getId(),
  'amount' => 1500,
]);

if ($entry != null) {
  do {
    $allGood = false;

    if (isset($_POST[$entry['EntryID'] . '-pay']) && bool($_POST[$entry['EntryID'] . '-pay'])) {
      $fee = 0;
      // Relace with if (true) once legacy period is over
      if (bool($entry['GalaFeeConstant'])) {
        $fee = \Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->withPointMovedRight(2)->toInt();
        $allGood = true;
      } else {
        if (isset($_POST[$entry['EntryID'] . '-pay']) && $_POST[$entry['EntryID'] . '-pay']) {
          if (isset($_POST[$entry['EntryID'] . '-amount']) && $_POST[$entry['EntryID'] . '-amount']) {
            $fee = $fee = \Brick\Math\BigDecimal::of((string) $_POST[$entry['EntryID'] . '-amount'])->withPointMovedRight(2)->toInt();
            $allGood = true;
            $updateTime->execute([
              number_format($_POST[$entry['EntryID'] . '-amount'], 2, '.', ''),
              $entry['EntryID']
            ]);
          } else {
            $fee = \Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->withPointMovedRight(2)->toInt();
            $allGood = true;
          }
        }
      }

      $galaData = new GalaPrices($db, $entry["GalaID"]);

      $itemEntries = [];
      foreach ($swimsArray as $key => $name) {
        if ($entry[$key]) {

          $swimAmount = (int) $galaData->getEvent($key)->getPrice();

          $itemEntries[] = [
            'name' => $name,
            'amount' => $swimAmount,
            'currency' => 'gbp',
            'attributes' => []
          ];
        }
      }

      if ($entry['pFee'] > 0) {
        $itemEntries[] = [
          'name' => 'Entry processing fee',
          'amount' => $entry['pFee'],
          'currency' => 'gbp',
          'attributes' => []
        ];
      }

      $checkoutSession->addItem([
        'name' => $entry['GalaName'],
        'description' => $entry['MForename'] . ' ' . $entry['MSurname'],
        'amount' => $fee,
        'sub_items' => $itemEntries,
        'attributes' => [
          'type' => 'gala_entry',
          'id' => $entry['EntryID'],
        ],
      ]);

      $payingEntries += [$entry['EntryID'] => [
        'Amount' => $fee,
        'UserEnteredAmount' => !$entry['GalaFeeConstant'],
        'Gala' => $entry['GalaID'],
        'Member' => $entry['MemberID']
      ]];
    }
  } while ($entry = $entries->fetch(PDO::FETCH_ASSOC));

  if (sizeof($payingEntries) > 0) {
    $checkoutSession->autoCalculateTotal();

    $checkoutSession->metadata['return']['url'] = autoUrl('galas');
    $checkoutSession->metadata['return']['instant'] = false;
    $checkoutSession->metadata['return']['buttonString'] = 'Return to gala page';

    $checkoutSession->metadata['cancel']['url'] = autoUrl('galas/pay-for-entries');

    $checkoutSession->save();
  } else {
    header("Location: " . autoUrl("galas/pay-for-entries"));

    // Call return, else overrides already sent headers further down
    return;
  }
} else {
  // Error
  header("Location: " . autoUrl("galas/pay-for-entries"));
}

header("Location: " . $checkoutSession->getUrl());
