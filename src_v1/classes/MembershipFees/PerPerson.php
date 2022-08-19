<?php

namespace MembershipFees;

class PerPerson extends ClassType
{

  public static function calculate($members, $fees, $partial = false)
  {
    $fee = 0;
    if (sizeof($fees) > 0) {
      $fee = $fees[0];
    }

    $items = [];

    for ($i = 0; $i < sizeof($members); $i++) {
      $member = $members[$i];
      if (($partial && bool($member['RR'])) || !$partial) {
        $thisFee = $fee;

        // If club pays, set to 0
        if (bool($member['ClubPaid'])) {
          $thisFee = 0;
        }

        $items[] = new MembershipFeeItem(
          $member['MForename'] . ' ' . $member['MSurname'],
          $thisFee,
          $member['MemberID']
        );
      }
    }

    return $items;
  }
}
