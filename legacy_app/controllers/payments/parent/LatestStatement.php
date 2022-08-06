<?php

$db = DB::connection()->getPdo();

try {
  $query = $db->prepare("SELECT PaymentID FROM payments WHERE UserID = ? ORDER BY `Date` DESC ");
  $query->execute([$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID']]);

  $payment = $query->fetchColumn();

  if ($payment == null) {
    header("Location: " . autoUrl("payments/statements"));
  } else {
    header("Location: " . autoUrl("payments/statements/" . $payment));
  }
} catch (Exception $e) {
  halt(404);
}
