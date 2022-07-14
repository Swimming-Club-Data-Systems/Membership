<?php

ignore_user_abort(true);
set_time_limit(0);

header("content-type: application/json");

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

$db = app()->db;
$tenant = app()->tenant;

$squadFeeMonths = [];
try {
  $squadFeeMonths = json_decode(app()->tenant->getKey('SquadFeeMonths'), true);
} catch (Exception | Error $e) {
  // Do nothing
}
$date = new DateTime('now', new DateTimeZone('Europe/London'));
$squadFeeRequired = true;
if (isset($squadFeeMonths[$date->format("m")])) {
  $squadFeeRequired = !bool($squadFeeMonths[$date->format("m")]);
}

if ($tenant->getBooleanKey('ENABLE_BILLING_SYSTEM')) {
  // Prepare things
  $getUserMembers = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, members.DateOfBirth FROM members WHERE UserID = ?");

  // GET TIER 3 STUFF
  $tier3Date = new DateTime('now', new DateTimeZone('Europe/London'));
  $dateToday = clone $date;

  $tier3 = $tenant->getKey('TIER3_SQUAD_FEES');
  if ($tier3) {
    $tier3 = json_decode($tier3, true);
    $tier3Date = new DateTime($tier3['eighteen_by'], new DateTimeZone('Europe/London'));
    $tier3Date->sub(new DateInterval('P18Y'));
  }

  $getSquadMetadata = $db->prepare("SELECT squads.SquadName, squads.SquadID, squads.SquadFee, squadMembers.Paying FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE squadMembers.Member = ?;");

  $getExtraMetadata = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, extras.ExtraName, extras.ExtraID, extras.ExtraFee, extras.Type FROM ((members INNER JOIN `extrasRelations` ON members.MemberID = extrasRelations.MemberID) INNER JOIN `extras` ON extras.ExtraID = extrasRelations.ExtraID) WHERE members.UserID = ? ORDER BY members.MForename ASC, members.MSurname ASC;");

  $track = $db->prepare("INSERT INTO `individualFeeTrack` (`MonthID`, `MemberID`, `UserID`, `Description`, `Amount`, `Type`, `PaymentID`) VALUES (?, ?, ?, ?, ?, ?, ?)");

  $addToPaymentsPending = $db->prepare("INSERT INTO `paymentsPending` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MetadataJSON`) VALUES (?, 'Pending', ?, ?, ?, 'GBP', 'Payment', ?);");

  $addCreditToPaymentsPending = $db->prepare("INSERT INTO `paymentsPending` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MetadataJSON`) VALUES (?, 'Pending', ?, ?, ?, 'GBP', 'Refund', ?);");

  $updateIndivFeeTrack = $db->prepare("UPDATE `individualFeeTrack` SET `PaymentID` = ? WHERE ID = ?;");

  $getMonthSum = $db->prepare("SELECT SUM(`Amount`) FROM `paymentsPending` WHERE `UserID` = ? AND `Status` = 'Pending' AND `Date` <= ? AND `Type` = ?;");

  $addPaymentForCharge = $db->prepare("INSERT INTO `payments` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MandateID`, `PMkey`) VALUES (?, ?, ?, ?, ?, 'GBP', 'Payment', ?, ?);");

  $setPaymentsPending = $db->prepare("UPDATE `paymentsPending` SET Payment = ?, `Status` = ? WHERE `UserID` = ? AND `Status` = 'Pending' AND `Date` <= ?");

  $setPaymentsPendingPM = $db->prepare("UPDATE `paymentsPending` SET `PMkey` = ? WHERE `UserID` = ? AND `Status` = 'Pending' AND `Date` <= ?");

  $getCountPending = $db->prepare("SELECT COUNT(*) FROM `paymentsPending` WHERE `UserID` = ? AND `Status` = 'Pending';");

  // Begin transaction
  $db->beginTransaction();

  try {

    $dateTime = new DateTime('first day of this month', new DateTimeZone('Europe/London'));
    $ms = $dateTime->format('Y-m');
    $date = $dateTime->format('Y-m-d');

    $sql = $db->prepare("SELECT * FROM `paymentMonths` WHERE Tenant = ? ORDER BY `Date` DESC LIMIT 1;");
    $sql->execute([
      $tenant->getId()
    ]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if ($row != null) {
      if ($row['MonthStart'] != $ms) {
        $sql = $db->prepare("INSERT INTO `paymentMonths` (`MonthStart`, `Date`, `Tenant`) VALUES (?, ?, ?);");
        $sql->execute([$ms, $date, $tenant->getId()]);
      }
    } else {
      $sql = $db->prepare("INSERT INTO `paymentMonths` (`MonthStart`, `Date`, `Tenant`) VALUES (?, ?, ?);");
      $sql->execute([$ms, $date, $tenant->getId()]);
    }

    $sql = $db->prepare("SELECT COUNT(*) FROM `paymentSquadFees` INNER JOIN `paymentMonths` ON paymentSquadFees.MonthID = paymentMonths.MonthID WHERE `MonthStart` = ? AND paymentMonths.Tenant = ? ORDER BY `Date` DESC LIMIT 1;");
    $sql->execute([$ms, $tenant->getId()]);
    if ($sql->fetchColumn() == 0) {
      $sql = $db->prepare("SELECT `MonthID` FROM `paymentMonths` WHERE `MonthStart` = ? AND Tenant = ? ORDER BY `Date` DESC LIMIT 1;");
      $sql->execute([$ms, $tenant->getId()]);
      $mid = $sql->fetchColumn();
      if ($mid == null) {
        throw new Exception("Month ID is NULL");
      }
      $sql = $db->prepare("INSERT INTO `paymentSquadFees` (`MonthID`, `Tenant`) VALUES (?, ?);");
      $sql->execute([
        $mid,
        $tenant->getId()
      ]);

      $sql = $db->prepare("SELECT `UserID` FROM `users` INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE users.Tenant = ? AND `Permission` = 'Parent' AND Active;");
      $sql->execute([
        $tenant->getId()
      ]);

      $cumulativeTotalCharged = 0;
      $currentTotalCharged = 0;
      $maxWeeklyThreshold = PHP_INT_MAX;

      $max = (int) $tenant->getKey('STRIPE_MAX_WEEKLY_BACS_THRESHOLD');

      if ($max > 0) {
        $maxWeeklyThreshold = $max;
      }

      while ($user = $sql->fetchColumn()) {

        if ($squadFeeRequired) {
          // Get user members
          $getUserMembers->execute([
            $user
          ]);

          $numMembers = 0;
          $discount = 0;

          $discountMembers = [];

          while ($member = $getUserMembers->fetch(PDO::FETCH_ASSOC)) {
            $getSquadMetadata->execute([
              $member['MemberID']
            ]);

            $paying = false;
            $memberTotal = 0;

            while ($squad = $getSquadMetadata->fetch(PDO::FETCH_ASSOC)) {
              if (bool($squad['Paying'])) {
                $paying = true;
              }

              $metadata = [
                "PaymentType" => "SquadFees",
                "type" => [
                  "object" => 'SquadFee',
                  "id" => $squad['SquadID'],
                  "name" => $squad['SquadName']
                ]
              ];
              $metadata = json_encode($metadata);

              $fee = BigDecimal::of((string) $squad['SquadFee'])->withPointMovedRight(2)->toInt();
              $memberTotal += $fee;

              $addToPaymentsPending->execute([
                $date,
                $user,
                $member['MForename'] . " " . $member['MSurname'] . ' - ' . $squad['SquadName'] . ' Squad Fees',
                $fee,
                $metadata
              ]);

              $paymentID = $db->lastInsertId();

              $track_info = [
                $mid,
                $member['MemberID'],
                $user,
                'Squad Fees (' . $squad['SquadName'] . ')',
                $fee,
                'SquadFee',
                $paymentID
              ];
              $track->execute($track_info);

              if (!bool($squad['Paying'])) {
                $addCreditToPaymentsPending->execute([
                  $date,
                  $user,
                  $member['MForename'] . " " . $member['MSurname'] . ' - ' . $squad['SquadName'] . ' Squad Fee Exemption',
                  $fee,
                  null
                ]);

                $memberTotal -= $fee;
              }

              // Tier 3
              $dob = new DateTime($member['DateOfBirth'], new DateTimeZone('Europe/London'));
              if ($dob <= $tier3Date && isset($tier3['squads'][(string) $squad['SquadID']]) && ((int) $tier3['squads'][(string) $squad['SquadID']]) > 0) {
                $addCreditToPaymentsPending->execute([
                  $date,
                  $user,
                  $member['MForename'] . " " . $member['MSurname'] . ' - ' . $squad['SquadName'] . ' Tier 3 Squad Fee Rebate',
                  min(((int) $tier3['squads'][(string) $squad['SquadID']]), $fee),
                  null
                ]);
              }
            }

            if ($paying) {
              $numMembers++;
            }

            if ($paying && app()->tenant->isCLS()) {
              $memberFees = [
                'fee' => $memberTotal,
                'member' => $member['MForename'] . " " . $member['MSurname']
              ];
              $discountMembers[] = $memberFees;
            }
          }

          // If is CLS handle discounts
          if (app()->tenant->isCLS()) {
            usort($discountMembers, function ($item1, $item2) {
              return $item2['fee'] <=> $item1['fee'];
            });

            $number = 0;
            foreach ($discountMembers as $member) {
              $number++;

              // Calculate discounts if required.
              // Always round discounted value down - Could save clubs pennies!
              $swimmerDiscount = 0;
              $discountPercent = '0';
              try {
                $memberTotalDec = \Brick\Math\BigInteger::of($member['fee'])->toBigDecimal()->withPointMovedLeft(2);
                if ($number == 3) {
                  // 20% discount applies
                  $swimmerDiscount = $memberTotalDec->multipliedBy('0.20')->toScale(2, RoundingMode::DOWN)->withPointMovedRight(2)->toInt();
                  $discountPercent = '20';
                } else if ($number > 3) {
                  // 40% discount applies
                  $swimmerDiscount = $memberTotalDec->multipliedBy('0.40')->toScale(2, RoundingMode::DOWN)->withPointMovedRight(2)->toInt();
                  $discountPercent = '40';
                }
              } catch (Exception $e) {
                // Something went wrong so ensure these stay zero!
                $swimmerDiscount = 0;
                $discountPercent = '0';
              }

              if ($swimmerDiscount > 0) {
                // Apply credit to account for discount
                $addCreditToPaymentsPending->execute([
                  $date,
                  $user,
                  $member['member'] . ' - Multi swimmer squad fee discount (' . $discountPercent . '%)',
                  $swimmerDiscount,
                  null
                ]);
              }
            }
          }
        }

        // Now calculate extra fees payable
        $getExtraMetadata->execute([$user]);

        while ($swimmerRow = $getExtraMetadata->fetch(PDO::FETCH_ASSOC)) {
          if ($swimmerRow['ExtraFee'] > 0) {
            $metadata = [
              "PaymentType"         => "ExtraFees",
              "type" => [
                "object" => 'ExtraFee',
                "id" => $swimmerRow['ExtraID'],
                "name" => $swimmerRow['ExtraName']
              ]
            ];

            $metadata = json_encode($metadata);

            $description = $swimmerRow['MForename'] . " " . $swimmerRow['MSurname'] . ' - ' . $swimmerRow['ExtraName'];
            $fee = BigDecimal::of((string) $swimmerRow['ExtraFee'])->withPointMovedRight(2)->toInt();

            if ($swimmerRow['Type'] == 'Payment') {
              $addToPaymentsPending->execute([
                $date,
                $user,
                $description,
                $fee,
                $metadata
              ]);
            } else if ($swimmerRow['Type'] == 'Refund') {
              $addCreditToPaymentsPending->execute([
                $date,
                $user,
                $description,
                $fee,
                $metadata
              ]);
            }

            $paymentID = $db->lastInsertId();

            $track_info = [
              $mid,
              $swimmerRow['MemberID'],
              $user,
              'Extra Fees (' . $swimmerRow['ExtraName'] . ')',
              $fee,
              'ExtraFee',
              $paymentID
            ];

            $track->execute($track_info);
          }
        }

        // Begin to sum up payments
        $getMonthSum->execute([$user, $date, 'Payment']);
        $amount = $getMonthSum->fetchColumn();

        $getMonthSum->execute([$user, $date, 'Refund']);
        $userDiscount = $getMonthSum->fetchColumn();

        $amount = $amount - $userDiscount;

        $getCountPending->execute([$user]);

        $dateString = date("F Y", strtotime("first day of this month")) . " DD";

        // Check date
        $cumulativeTotalCharged += $amount;
        $currentTotalCharged += $amount;

        if ($currentTotalCharged > $maxWeeklyThreshold) {
          // Advance date forward 7 days and reset currentTotalCharged

          $dateObj = new DateTime($date, new DateTimeZone('Europe/London'));
          $dateObj->add(new DateInterval('P1W'));
          $date = $dateObj->format('Y-m-d');

          $currentTotalCharged = $amount;
        }

        // If amount is too low, it will wait for the next payment round
        if ($amount > 100) {
          $addPaymentForCharge->execute([
            $date,
            'pending_api_request',
            $user,
            $dateString,
            $amount,
            null,
            null
          ]);
          $id = $db->lastInsertId();
          $setPaymentsPending->execute([
            $id,
            'Queued',
            $user,
            $date
          ]);
        } else if ($amount == 0 && $getCountPending->fetchColumn() > 0) {
          $addPaymentForCharge->execute([
            $date,
            'paid_out',
            $user,
            $dateString,
            $amount,
            null,
            null
          ]);
          $id = $db->lastInsertId();
          $setKey = $db->prepare("UPDATE payments SET PMkey = ? WHERE PaymentID = ?");
          $setKey->execute([
            'NA' . $id,
            $id
          ]);
          $setPaymentsPending->execute([
            $id,
            'Paid',
            $user,
            $date
          ]);

          // Set PM key - NOT NEEDED SOON
          $setPaymentsPendingPM->execute([
            'NA' . $id,
            $user,
            $date
          ]);
        }
      }

      // Add Swimmers with No Parent to Fee Tracker
      if ($squadFeeRequired) {
        // // Squad Fees
        $sql = $db->prepare("SELECT `members`.`MemberID`, `SquadFee`, `SquadName` FROM ((`members` INNER JOIN squadMembers ON members.MemberID = squadMembers.Member) INNER JOIN `squads` ON squads.SquadID = squadMembers.Squad) WHERE members.Tenant = ? AND `members`.`UserID` IS NULL");
        $sql->execute([
          $tenant->getId()
        ]);

        $add_track = $db->prepare("INSERT INTO `individualFeeTrack` (`MonthID`, `PaymentID`, `MemberID`, `UserID`, `Amount`, `Description`, `Type`, `NC`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
          // Add to tracker
          if ($row['SquadFee'] > 0) {
            $add_track->execute([
              $mid,
              null,
              $row['MemberID'],
              null,
              BigDecimal::of((string) $row['SquadFee'])->withPointMovedRight(2)->toInt(),
              "Squad Fees (" . $row['SquadName'] . ")",
              'SquadFee',
              true
            ]);
          }
        }
      }

      $sql = $db->prepare("SELECT `members`.`MemberID`, `ExtraFee`, `ExtraName` FROM ((`members` INNER JOIN `extrasRelations` ON extrasRelations.MemberID = members.MemberID) INNER JOIN `extras` ON extrasRelations.ExtraID = extras.ExtraID) WHERE members.Tenant = ? AND `members`.`UserID` IS NULL");
      $sql->execute([
        $tenant->getId()
      ]);

      while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
        // Add to tracker
        if ($row['ExtraFee'] > 0) {
          $add_track->execute([
            $mid,
            null,
            $row['MemberID'],
            null,
            BigDecimal::of((string) $row['ExtraFee'])->withPointMovedRight(2)->toInt(),
            "Extra Fees (" . $row['ExtraName'] . ")",
            'ExtraFee',
            true
          ]);
        }
      }
    }

    // Commit operations
    $db->commit();
    echo json_encode([
      'success' => true,
      'message' => "Operation Successful"
    ]);
  } catch (Exception $e) {
    $db->rollBack();
    reportError($e);

    echo json_encode([
      'success' => false,
      'message' => $e->getMessage(),
    ]);
  }
} else {
  echo json_encode([
    'success' => false,
    'message' => "Billing system disabled"
  ]);
}
