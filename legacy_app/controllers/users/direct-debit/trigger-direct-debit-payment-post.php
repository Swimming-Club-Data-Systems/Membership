<?php

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $id
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

// Get Stripe direct debit info
$getStripeDD = $db->prepare("SELECT stripeMandates.ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1;");
if (stripeDirectDebit()) {
  $getStripeDD->execute([
    $id
  ]);
}
$stripeDD = $getStripeDD->fetch(PDO::FETCH_ASSOC);

$bankName = $bank = $has_logo = $logo_path = null;
$hasGC = false;
if (userHasMandates($id)) {
  $bankName = mb_strtoupper(bankDetails($id, "account_holder_name"));
  if ($bankName != "UNKNOWN") {
    $bankName = $bankName . ', ';
  } else {
    $bankName = null;
  }
  $bank = mb_strtoupper(bankDetails($id, "bank_name"));
  $logo_path = getBankLogo($bank);
  $hasGC = true;
}

$dateTime = new DateTime('first day of this month', new DateTimeZone('Europe/London'));
$ms = $dateTime->format('Y-m');

$dateTime = new DateTime('now', new DateTimeZone('Europe/London'));
$date = $dateTime->format('Y-m-d');

$sql = $db->prepare("SELECT COUNT(*) FROM `paymentMonths` WHERE Tenant = ? AND MonthStart = ? ORDER BY `Date` DESC LIMIT 1;");
$sql->execute([
  $tenant->getId(),
  $ms,
]);
$monthExists = $sql->fetchColumn() > 0;

if (!$monthExists) halt(409);

$sql = $db->prepare("SELECT `MonthID` FROM `paymentMonths` WHERE `MonthStart` = ? AND Tenant = ? ORDER BY `Date` DESC LIMIT 1;");
$sql->execute([$ms, $tenant->getId()]);
$monthId = $sql->fetchColumn();
if (!$monthId) throw new Exception("Month ID is NULL");

$getPending = $db->prepare("SELECT `PaymentID` `id`, `Date` `date`, `Name` `name`, `Amount` `amount`, `Currency` `currency`, `Type` `type` FROM `paymentsPending` WHERE `UserID` = ? AND `PMkey` IS NULL AND `Status` = 'Pending' ORDER BY `Date` DESC");
$getPending->execute([$id]);

$getSquadMetadata = $db->prepare("SELECT members.MemberID memberId, members.MForename forename, members.MSurname surname, squads.SquadName squad, squads.SquadID squadId, squads.SquadFee fee FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad INNER JOIN members ON members.MemberID = squadMembers.Member WHERE members.UserID = ? AND squadMembers.Paying;");
$getSquadMetadata->execute([
  $id,
]);

$getExtraMetadata = $db->prepare("SELECT members.MemberID memberId, members.MForename forename, members.MSurname surname, extras.ExtraName extra, extras.ExtraID extraId, extras.ExtraFee fee, extras.Type `type` FROM ((members INNER JOIN `extrasRelations` ON members.MemberID = extrasRelations.MemberID) INNER JOIN `extras` ON extras.ExtraID = extrasRelations.ExtraID) WHERE members.UserID = ? ORDER BY members.MForename ASC, members.MSurname ASC;");
$getExtraMetadata->execute([
  $id,
]);

$track = $db->prepare("INSERT INTO `individualFeeTrack` (`MonthID`, `MemberID`, `UserID`, `Description`, `Amount`, `Type`, `PaymentID`) VALUES (?, ?, ?, ?, ?, ?, ?)");

$addToPaymentsPending = $db->prepare("INSERT INTO `paymentsPending` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MetadataJSON`) VALUES (?, 'Pending', ?, ?, ?, 'GBP', 'Payment', ?);");

$addCreditToPaymentsPending = $db->prepare("INSERT INTO `paymentsPending` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MetadataJSON`) VALUES (?, 'Pending', ?, ?, ?, 'GBP', 'Refund', ?);");

$setPaymentsPending = $db->prepare("UPDATE `paymentsPending` SET Payment = ?, `Status` = ? WHERE `PaymentID` = ?");

$addPaymentForCharge = $db->prepare("INSERT INTO `payments` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MandateID`, `PMkey`) VALUES (?, ?, ?, ?, ?, 'GBP', 'Payment', ?, ?);");

$total = 0;
$itemIds = [];

header("content-type: application/json");

try {

  $db->beginTransaction();

  while ($squadFee = $getSquadMetadata->fetch(PDO::FETCH_OBJ)) {

    if (isset($_POST["squad-fee-" . $squadFee->squadId . "-" . $squadFee->memberId]) && bool($_POST["squad-fee-" . $squadFee->squadId . "-" . $squadFee->memberId])) {
      $metadata = [
        "PaymentType" => "SquadFees",
        "type" => [
          "object" => 'SquadFee',
          "id" => $squadFee->squadId,
          "name" => $squadFee->squad
        ]
      ];
      $metadata = json_encode($metadata);

      $fee = BigDecimal::of((string) $squadFee->fee)->withPointMovedRight(2)->toInt();

      $addToPaymentsPending->execute([
        $date,
        $id,
        $squadFee->forename . " " . $squadFee->surname . ' - ' . $squadFee->squad . ' Squad Fees',
        $fee,
        $metadata
      ]);

      $paymentID = $db->lastInsertId();

      $itemIds[] = $paymentID;

      $track_info = [
        $monthId,
        $squadFee->memberId,
        $id,
        'Squad Fees (' . $squadFee->squad . ')',
        $fee,
        'SquadFee',
        $paymentID
      ];
      $track->execute($track_info);

      $total += $fee;
    }
  }

  while ($extraFee = $getExtraMetadata->fetch(PDO::FETCH_OBJ)) {

    if (isset($_POST["extra-fee-" . $extraFee->extraId . "-" . $extraFee->memberId]) && bool($_POST["extra-fee-" . $extraFee->extraId . "-" . $extraFee->memberId])) {
      $metadata = [
        "PaymentType"         => "ExtraFees",
        "type" => [
          "object" => 'ExtraFee',
          "id" => $extraFee->extraId,
          "name" => $extraFee->extra
        ]
      ];

      $metadata = json_encode($metadata);

      $description = $extraFee->forename . " " . $extraFee->surname . ' - ' . $extraFee->extraId;
      $fee = BigDecimal::of((string) $extraFee->fee)->withPointMovedRight(2)->toInt();

      if ($extraFee->type == 'Payment') {
        $addToPaymentsPending->execute([
          $date,
          $id,
          $description,
          $fee,
          $metadata
        ]);

        $total += $fee;
      } else if ($extraFee->type == 'Refund') {
        $addCreditToPaymentsPending->execute([
          $date,
          $id,
          $description,
          $fee,
          $metadata
        ]);

        $total -= $fee;
      }

      $paymentID = $db->lastInsertId();

      $itemIds[] = $paymentID;

      $track_info = [
        $monthId,
        $extraFee->memberId,
        $id,
        'Extra Fees (' . $extraFee->extra . ')',
        $fee,
        'ExtraFee',
        $paymentID
      ];

      $track->execute($track_info);
    }
  }

  while ($item = $getPending->fetch(PDO::FETCH_OBJ)) {
    if (isset($_POST["invoice-item-" . $item->id]) && bool($_POST["invoice-item-" . $item->id])) {

      // Do fee
      $fee = $item->amount;

      if ($item->type == 'Refund') {
        $total -= $fee;
      } else {
        $total += $fee;
      }

      $itemIds[] = $item->id;
    }
  }

  if ($total < 100) {
    throw new Exception('Total amount too low');
  }

  $addPaymentForCharge->execute([
    $date,
    'pending_api_request',
    $id,
    'Early Payment',
    $total,
    null,
    null
  ]);

  $paymentId = $db->lastInsertId();

  foreach ($itemIds as $itemId) {
    $setPaymentsPending->execute([
      $paymentId,
      'Queued',
      $itemId
    ]);
  }

  $db->commit();

  http_response_code(200);
  echo json_encode([
    'status' => 200,
  ]);
} catch (Exception $e) {
  $db->rollBack();
  if ($e->getMessage() != 'Total amount too low') {
    reportError($e);
  }

  http_response_code(500);
  echo json_encode([
    'status' => 500,
  ]);
}
