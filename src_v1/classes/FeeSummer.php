<?php

/**
 * Class to sum fees for users etc and write to db or return objects
 * 
 * @author Chris Heppell
 * @copyright SCDS
 * 
 */

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class FeeSummer
{

  // Array for users and their payment items
  private array $users;
  private int $month;
  private $squadFeeRequired = true;

  /**
   * Needs to do
   * All users
   * Single user
   * Writing to memory
   * Writing to db
   */

  /**
   * Create a FeeSummer object
   * 
   * @param int month the month of the year to use when calculating fees
   */
  public function __construct(int $feeMonth)
  {

    $db = app()->db;
    $tenant = app()->tenant;

    $squadFeeMonths = [];
    try {
      $squadFeeMonths = json_decode(app()->tenant->getKey('SquadFeeMonths'), true);
    } catch (Exception | Error $e) {
      // Do nothing
    }
    if (isset($squadFeeMonths[(string) $feeMonth])) {
      $this->squadFeeRequired = !bool($squadFeeMonths[(string) $feeMonth]);
    }
  }

  public function sumUser(int $user, $ignoreMonth = false)
  {

    // Prepare queries
    $db = app()->db;
    $dateObject = new DateTime('now', new DateTimeZone('Europe/London'));
    $date = $dateObject->format("Y-m-d");

    $getUserMembers = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname FROM members WHERE UserID = ?");

    $getSquadMetadata = $db->prepare("SELECT squads.SquadName, squads.SquadID, squads.SquadFee, squadMembers.Paying FROM squads INNER JOIN squadMembers ON squads.SquadID = squadMembers.Squad WHERE squadMembers.Member = ?;");

    $getExtraMetadata = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, extras.ExtraName, extras.ExtraID, extras.ExtraFee, extras.Type FROM ((members INNER JOIN `extrasRelations` ON members.MemberID = extrasRelations.MemberID) INNER JOIN `extras` ON extras.ExtraID = extrasRelations.ExtraID) WHERE members.UserID = ? ORDER BY members.MForename ASC, members.MSurname ASC;");

    // Arrays for payment items
    $existingItems = [];
    $provisionalItems = [];

    // Totals
    $credits = 0;
    $debits = 0;
    $squadsTotal = 0;
    $extrasTotal = 0;

    if ($this->squadFeeRequired || $ignoreMonth) {
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
          $debits += $fee;
          $squadsTotal += $fee;

          $provisionalItems[] = [
            'date' => $date,
            'user' => $user,
            'description' => $member['MForename'] . " " . $member['MSurname'] . ' - ' . $squad['SquadName'] . ' Squad Fees',
            'amount' => $fee,
            'type' => 'Payment',
            'metadata' => $metadata,
            'needs_tracking' => true,
            'tracking' => [
              'month' => null, // Month ID
              'member' => $member['MemberID'],
              'user' => $user,
              'description' => 'Squad Fees (' . $squad['SquadName'] . ')',
              'amount' => $fee,
              'type' => 'SquadFee',
              'payments_pending_item' => null,
              'payment_item_id' => null,
              'payment_id' => null,
            ]
          ];

          if (!bool($squad['Paying'])) {
            $provisionalItems[] = [
              'date' => $date,
              'user' => $user,
              'description' => $member['MForename'] . " " . $member['MSurname'] . ' - ' . $squad['SquadName'] . ' Squad Fee Exemption',
              'amount' => $fee,
              'type' => 'Refund',
              'metadata' => null,
              'needs_tracking' => false,
              'tracking' => null,
              'payment_item_id' => null,
              'payment_id' => null,
            ];

            $memberTotal -= $fee;
            $squadsTotal -= $fee;
            $credits += $fee;
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
            $provisionalItems[] = [
              'date' => $date,
              'user' => $user,
              'description' => $member['member'] . ' - Multi swimmer squad fee discount (' . $discountPercent . '%)',
              'amount' => $swimmerDiscount,
              'type' => 'Refund',
              'metadata' => null,
              'needs_tracking' => false,
              'tracking' => null,
              'payment_item_id' => null,
              'payment_id' => null,
            ];
            $squadsTotal -= $swimmerDiscount;
            $credits += $swimmerDiscount;
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

        $provisionalItems[] = [
          'date' => $date,
          'user' => $user,
          'description' => $description,
          'amount' => $fee,
          'type' => $swimmerRow['Type'],
          'metadata' => $metadata,
          'needs_tracking' => true,
          'tracking' => [
            'month' => null, // Month ID
            'member' => $swimmerRow['MemberID'],
            'user' => $user,
            'description' => 'Extra Fees (' . $swimmerRow['ExtraName'] . ')',
            'amount' => $fee,
            'type' => 'ExtraFee',
            'payments_pending_item' => null,
          ],
          'payment_item_id' => null,
          'payment_id' => null,
        ];

        if ($swimmerRow['Type'] == 'Payment') {
          $debits += $fee;
          $extrasTotal += $fee;
        } else if ($swimmerRow['Type'] == 'Refund') {
          $credits += $fee;
          $extrasTotal -= $fee;
        }
      }
    }

    // Get existing pending fees
    $getMonthItems = $db->prepare("SELECT `PaymentID`, `Date`, `UserID`, `Name`, `Amount`, `Type`, `MetadataJSON`, `Payment` FROM `paymentsPending` WHERE `UserID` = ? AND `Status` = 'Pending' AND `Date` <= ?;");
    $getMonthItems->execute([
      $user,
      $date,
    ]);

    while ($item = $getMonthItems->fetch(PDO::FETCH_ASSOC)) {
      $existingItems[] = [
        'date' => $item['Date'],
        'user' => $item['UserID'],
        'description' => $item['Name'],
        'amount' => $item['Amount'],
        'type' => $item['Type'],
        'metadata' => $item['MetadataJSON'],
        'needs_tracking' => false,
        'tracking' => null,
        'payment_item_id' => $item['PaymentID'],
        'payment_id' => $item['Payment'],
      ];

      if ($item['Type'] == 'Payment') {
        $debits += (int) $item['Amount'];
      } else if ($item['Type'] == 'Refund') {
        $credits += (int) $item['Amount'];
      }
    }

    $getName = $db->prepare("SELECT Forename, Surname FROM users WHERE UserID = ?");
    $getName->execute([
      $user,
    ]);
    $name = $getName->fetch(PDO::FETCH_ASSOC);

    // Return data
    return [
      'user' => $user,
      'forename' => $name['Forename'],
      'surname' => $name['Surname'],
      'total' => $debits - $credits,
      'credits' => $credits,
      'debits' => $debits,
      'squad_total' => $squadsTotal,
      'extra_total' => $extrasTotal,
      'items' => [
        'existing' => $existingItems,
        'provisional' => $provisionalItems,
      ]
    ];
  }

  public function sumAll()
  {
    $db = app()->db;
    $tenant = app()->tenant;

    $users = [];

    $sql = $db->prepare("SELECT `UserID` FROM `users` INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE users.Tenant = ? AND `Permission` = 'Parent' AND Active ORDER BY Forename ASC, Surname ASC;");
    $sql->execute([
      $tenant->getId()
    ]);
    while ($user = $sql->fetchColumn()) {

      // try {
        $users[] = $this->sumUser($user);
      // } catch (Exception $e) {
        // reportError($e);
      // }

    }

    return $users;
  }

  public function persistData()
  {
    $db = app()->db;

    $track = $db->prepare("INSERT INTO `individualFeeTrack` (`MonthID`, `MemberID`, `UserID`, `Description`, `Amount`, `Type`, `PaymentID`) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $addToPaymentsPending = $db->prepare("INSERT INTO `paymentsPending` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MetadataJSON`) VALUES (?, 'Pending', ?, ?, ?, 'GBP', 'Payment', ?);");

    $addCreditToPaymentsPending = $db->prepare("INSERT INTO `paymentsPending` (`Date`, `Status`, `UserID`, `Name`, `Amount`, `Currency`, `Type`, `MetadataJSON`) VALUES (?, 'Pending', ?, ?, ?, 'GBP', 'Refund', ?);");
  }

  public function getData()
  {
  }
}
