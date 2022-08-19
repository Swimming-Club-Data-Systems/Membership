<?php

$db = app()->db;
$tenant = app()->adminCurrentTenant;

if (isset($_POST['default-mandate']) && \SCDS\CSRF::verify()) {

  $getCount = $db->prepare("SELECT COUNT(*) FROM tenantPaymentMethods INNER JOIN tenantStripeCustomers ON tenantPaymentMethods.Customer = tenantStripeCustomers.CustomerID WHERE Usable AND Tenant = ? AND `Type` = 'bacs_debit' AND tenantPaymentMethods.ID = ?");
  $getCount->execute([
    $tenant->getId(),
    $_POST['default-mandate'],
  ]);

  if ($getCount->fetchColumn() > 0) {
    $tenant->setKey('DEFAULT_PAYMENT_MANDATE', $_POST['default-mandate']);
    $_SESSION['Saved_DD_Default'] = true;
  }

  http_response_code(302);
  header("location: " . autoUrl('payments-admin/direct-debit-instruction'));
} else {
  halt(404);
}
