<?php

namespace SCDS\Checkout\ItemHandlers;

class GalaEntry {

  public static function paid($item, $stripePayment) {

    $db = app()->db;

    $setCharged = $db->prepare("UPDATE galaEntries SET Charged = ?, StripePayment = ? WHERE EntryID = ?");
    $setCharged->execute([
      true,
      $stripePayment,
      $item->attributes->id,
    ]);

    $addPaymentItems = $db->prepare("INSERT INTO stripePaymentItems (Payment, `Name`, `Description`, Amount, Currency, AmountRefunded) VALUES (?, ?, ?, ?, ?, ?)");
    $addPaymentItems->execute([
      $stripePayment,
      'Gala entry',
      'Gala entry number ' . $item->attributes->id,
      $item->amount,
      $item->currency,
      0,
    ]);


  }

}