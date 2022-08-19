<?php

/**
 * REF IDs IN PAYMENTS-PENDING
 */

$items = $db->query(
  "SELECT PMkey, PaymentID FROM payments;"
);

$setId = $db->prepare("UPDATE paymentsPending SET Payment = ? WHERE PMkey = ?");

while ($item = $items->fetch(PDO::FETCH_ASSOC)) {
  if ($item['PaymentID'] != null) {
    $setId->execute([
      $item['PaymentID'],
      $item['PMkey']
    ]);
  }
}