<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = $db->prepare("SELECT COUNT(*) FROM payments INNER JOIN users ON users.UserID = payments.UserID WHERE PaymentID = ? AND users.Tenant = ?");
$sql->execute([
  $id,
  $tenant->getId()
]);
if ($sql->fetchColumn() == 0) {
  halt(404);
}

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

if ($_SESSION['TENANT-' . app()->tenant->getId()]['Token' . $id] == $token) {
  $db->prepare("UPDATE payments SET Status='paid_manually' WHERE PaymentID = ?")->execute([$id]);
  $db->prepare("UPDATE paymentsPending SET `Status` = 'Paid' WHERE Payment = ?")->execute([$id]);
} else {
  halt(404);
}

unset($_SESSION['TENANT-' . app()->tenant->getId()]['Token' . $id]);

header("Location: " . autoUrl("payments/statements/" . $id));
