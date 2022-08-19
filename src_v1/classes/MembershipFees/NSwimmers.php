<?php

namespace MembershipFees;

class NSwimmers extends ClassType
{

  public static function calculate($allMembers, $fees, $partial = false)
  {
    $items = [];
    $existingItems = [];

    $members = [];
    $paidMembers = [];
    foreach ($allMembers as $member) {
      if (!bool($member['ClubPaid'])) {
        $members[] = $member;
      } else {
        $paidMembers[] = $member;
      }
    }

    $fee = $originalFee = 0;
    if (sizeof($fees) > 0) {
      $originalFee = $fee = $fees[min(sizeof($fees) - 1, sizeof($members) - 1)];
    }

    for ($i = 0; $i < sizeof($members); $i++) {
      if ($i > 0) {
        $fee = 0;
      }
      $member = $members[$i];
      $items[] = new MembershipFeeItem(
        $member['MForename'] . ' ' . $member['MSurname'],
        $fee,
        $member['MemberID']
      );
    }

    if ($partial) {
      // Get list of partial members
      $existingMembers = [];
      for ($i = 0; $i < sizeof($members); $i++) {
        if (!bool($members[$i]['RR'])) {
          $existingMembers[] = $members[$i];
        }
      }

      $paidFee = 0;
      if (sizeof($fees) > 0) {
        $paidFee = $fees[min(sizeof($fees) - 1, sizeof($existingMembers) - 1)];
      }

      $fee = max(0, $originalFee - $paidFee);
      if (sizeof($items) > 0) {
        $items[0]->setAmount($fee);
      }
    }

    foreach ($paidMembers as $member) {
      $items[] = new MembershipFeeItem(
        $member['MForename'] . ' ' . $member['MSurname'] . ' (Exempt from fee)',
        0,
        $member['MemberID']
      );
    }

    return $items;
  }
}
