<?php

/**
 * REF IDs IN PAYMENTS-PENDING
 */

$db->query(
  "ALTER TABLE `paymentsPending`
    ADD COLUMN `Payment` int DEFAULT NULL,
    ADD FOREIGN KEY paymentTotalRef(Payment) REFERENCES payments(PaymentID) ON DELETE CASCADE;"
);

$items = $db->query(
  "SELECT DISTINCT PMkey FROM paymentsPending;"
);

$getId = $db->prepare("SELECT PaymentID FROM payments WHERE PMkey = ?");
$setId = $db->prepare("UPDATE paymentsPending SET Payment = ? WHERE PMkey = ?");

while ($item = $items->fetchColumn()) {
  $getId->execute([$item]);
  $paymentId = $getId->fetchColumn();
  if ($paymentId != null) {
    $setId->execute([
      $paymentId,
      $item
    ]);
  }
}