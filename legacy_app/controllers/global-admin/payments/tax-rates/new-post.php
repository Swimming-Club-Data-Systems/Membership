<?php

$db = app()->db;

try {

  if (!\SCDS\CSRF::verify()) throw new Exception('CSRF validity error');

  $insert = $db->prepare("INSERT INTO `tenantPaymentTaxRates` (`ID`, `Name`, `Type`, `Region`, `Rate`, `InclusiveExclusive`) VALUES (?, ?, ?, ?, ?, ?);");
  $insert->execute([
    Ramsey\Uuid\Uuid::uuid4()->toString(),
    $_POST['rate-name'],
    $_POST['rate-type'],
    $_POST['rate-region'],
    $_POST['rate'],
    $_POST['rate-in-ex'],
  ]);

  $_SESSION['TaxRateAddSuccess'] = true;
  http_response_code(302);
  header("location: " . autoUrl('admin/payments/tax-rates'));
} catch (Exception $e) {

  $_SESSION['TaxRateAddError'] = $e->getMessage();
  http_response_code(302);
  header("location: " . autoUrl('admin/payments/tax-rates/new'));
}
