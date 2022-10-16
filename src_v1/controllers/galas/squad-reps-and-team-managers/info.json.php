<?php

$db = app()->db;
$tenant = app()->tenant;

$squad = 'all';

if (isset($_GET['squad'])) {
  // Verify this squad is allowed for the user
  if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent' && $_GET['squad'] == 'all') {
    $squad = "all";
  } else {
    $squad = (int) $_GET['squad'];

    if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {

      // See if this squad is allowed
      $isAllowed = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ? AND Squad = ?");
      $isAllowed->execute([
        $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
        $squad
      ]);
      if ($isAllowed->fetchColumn() == 0) {
        // User cannot access this squad
        $noSquad = true;

        // Now if user has no squads, halt
        $isAllowed = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
        $isAllowed->execute([
          $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
        ]);
        if ($isAllowed->fetchColumn() == 0) {
          // User is not a squad rep
          halt(404);
        }
      }
    }
  }
}

$getGala = $db->prepare("SELECT GalaName `name`, GalaFee fee, GalaVenue venue, GalaFeeConstant fixed, GalaDate, RequiresApproval FROM galas WHERE GalaID = ? AND Tenant = ?");
$getGala->execute([
  $id,
  $tenant->getId()
]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if (!$gala) {
  halt(404);
}

$galaData = new GalaPrices($db, $id);

$getEntries = null;
$squadInfo = [];
if ($squad == "all") {
  $squadInfo = [
    'id' => 'all',
    'name' => 'All',
  ];
} else {
  $getSquad = $db->prepare("SELECT SquadID id, SquadName `name` FROM squads WHERE SquadID = ?");
  $getSquad->execute([
    $squad
  ]);
  $squadInfo = $getSquad->fetch(PDO::FETCH_ASSOC);
}

if ($gala == null || ($squadInfo == null && !$doNotHalt)) {
  halt(404);
} else if ($squad === 0 || $squad === null) {
  $noSquad = true;
}

if ($squad == "all") {
  $getEntries = $db->prepare("SELECT members.UserID `user`, DateOfBirth, GalaDate, 25Free, 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 25Back, 50Back, 100Back, 200Back, 25Breast, 50Breast, 100Breast, 200Breast, 25Fly, 50Fly, 100Fly, 200Fly, 100IM, 150IM, 200IM, 400IM, 25FreeTime, 50FreeTime, 100FreeTime, 200FreeTime, 400FreeTime, 800FreeTime, 1500FreeTime, 25BackTime, 50BackTime, 100BackTime, 200BackTime, 25BreastTime, 50BreastTime, 100BreastTime, 200BreastTime, 25FlyTime, 50FlyTime, 100FlyTime, 200FlyTime, 100IMTime, 150IMTime, 200IMTime, 400IMTime, MForename, MSurname, EntryID, Charged, FeeToPay, MandateID, EntryProcessed Processed, Refunded, galaEntries.AmountRefunded, galaEntries.PaymentID StatementItem, Intent, stripePayMethods.Brand, stripePayMethods.Last4, Funding, members.ASANumber, Approved FROM ((((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN users ON members.UserID = users.UserID) LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) LEFT JOIN stripePayments ON galaEntries.StripePayment = stripePayments.ID) LEFT JOIN stripePayMethods ON stripePayMethods.ID = stripePayments.Method) WHERE galaEntries.GalaID = ? ORDER BY MForename ASC, MSurname ASC");
  $getEntries->execute([$id]);
} else {
  $getEntries = $db->prepare("SELECT members.UserID `user`, DateOfBirth, GalaDate, 25Free, 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 25Back, 50Back, 100Back, 200Back, 25Breast, 50Breast, 100Breast, 200Breast, 25Fly, 50Fly, 100Fly, 200Fly, 100IM, 150IM, 200IM, 400IM, 25FreeTime, 50FreeTime, 100FreeTime, 200FreeTime, 400FreeTime, 800FreeTime, 1500FreeTime, 25BackTime, 50BackTime, 100BackTime, 200BackTime, 25BreastTime, 50BreastTime, 100BreastTime, 200BreastTime, 25FlyTime, 50FlyTime, 100FlyTime, 200FlyTime, 100IMTime, 150IMTime, 200IMTime, 400IMTime, MForename, MSurname, EntryID, Charged, FeeToPay, MandateID, EntryProcessed Processed, Refunded, galaEntries.AmountRefunded, galaEntries.PaymentID StatementItem, Intent, stripePayMethods.Brand, stripePayMethods.Last4, Funding, members.ASANumber, Approved FROM (((((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN users ON members.UserID = users.UserID) LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) LEFT JOIN stripePayments ON galaEntries.StripePayment = stripePayments.ID) LEFT JOIN stripePayMethods ON stripePayMethods.ID = stripePayments.Method) INNER JOIN squadMembers ON members.MemberId = squadMembers.Member) WHERE galaEntries.GalaID = ? AND squadMembers.Squad = ? ORDER BY MForename ASC, MSurname ASC");
  $getEntries->execute([$id, $squad]);
}

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

$today = new DateTime('now', new DateTimeZone('Europe/London'));
$lastDay = new DateTime($gala['GalaDate'], new DateTimeZone('Europe/London'));
$endOfYear = new DateTime('Last day of December ' . $lastDay->format("Y"), new DateTimeZone('Europe/London'));

$entries = [];
while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) {
  $birthday = new DateTime($entry['DateOfBirth'], new DateTimeZone('Europe/London'));

  $ageToday = $birthday->diff($today)->format('%y');
  $ageLastDay = $birthday->diff($lastDay)->format('%y');
  $ageEndOfYear = $birthday->diff($endOfYear)->format('%y');

  $events = [];
  foreach ($swimsArray as $key => $value) {
    $entryTime = null;
    if (bool($entry[$key])) {
      $entryTime = $entry[$key . 'Time'];
    }
    $events[] = [
      'event' => $key,
      'selected' => bool($entry[$key]),
      'name' => $value,
      'entry_time' => $entryTime,
      'allowed' => $galaData->getEvent($key)->isEnabled(),
      'price' => $galaData->getEvent($key)->getPrice(),
      'price_string' => $galaData->getEvent($key)->getPriceAsString(),
    ];
  }
  $entries[] = [
    'id' => (int) $entry['EntryID'],
    'user' => (int) $entry['user'],
    'forename' => $entry['MForename'],
    'surname' => $entry['MSurname'],
    'asa_number' => $entry['ASANumber'],
    'age_today' => $ageToday,
    'age_on_last_day' => $ageLastDay,
    'age_at_end_of_year' => $ageEndOfYear,
    'events' => $events,
    'charged' => bool($entry['Charged']),
    'charge_lock' => ($entry['Intent'] || $entry['StatementItem']),
    'amount_charged' => \Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->withPointMovedRight(2)->toInt(),
    'amount_charged_string' => (string) \Brick\Math\BigDecimal::of((string) $entry['FeeToPay'])->toScale(2),
    'refunded' => bool($entry['Refunded']),
    'amount_refunded' => (int) $entry['AmountRefunded'],
    'amount_refunded_string' => (string) (\Brick\Math\BigDecimal::of((string) $entry['AmountRefunded'])->withPointMovedLeft(2)->toScale(2)),
    'payment_intent' => [
      'id' => $entry['Intent'],
      'brand' => $entry['Brand'],
      'last4' => $entry['Last4'],
      'funding' => $entry['Funding'],
    ],
    'payment_item' => [
      'id' => $entry['StatementItem'],
    ],
    'processed' => bool($entry['Processed']),
    'approved' => bool($entry['Approved']),
    'mandate' => [
      'id' => (int) $entry['MandateID'],
    ]
  ];
}

$jsonArray = [
  'gala' => [
    'id' => (int) $id,
    'name' => $gala['name'],
    'venue' => $gala['venue'],
    'entries_require_approval' => bool($gala['RequiresApproval'])
  ],
  'squad' => [
    'id' => (int) $squadInfo['id'],
    'name' => $squadInfo['name']
  ],
  'entries' => $entries
];

$output = json_encode($jsonArray);