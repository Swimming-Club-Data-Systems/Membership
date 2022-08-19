<?php

$db = app()->db;

try {

  if (!\SCDS\CSRF::verify()) throw new Exception('CSRF validity error');

  $description = null;
  if (isset($_POST['product-description']) && mb_strlen(trim($_POST['product-description']))) {
    $description = trim($_POST['product-description']);
  }

  $id = Ramsey\Uuid\Uuid::uuid4()->toString();
  $insert = $db->prepare("INSERT INTO `tenantPaymentProducts` (`ID`, `Name`, `Description`) VALUES (?, ?, ?);");
  $insert->execute([
    $id,
    $_POST['product-name'],
    $description,
  ]);

  $_SESSION['ProductAddSuccess'] = true;
  http_response_code(302);
  header("location: " . autoUrl("admin/payments/products/$id"));
} catch (Exception $e) {

  $_SESSION['ProductAddError'] = $e->getMessage();
  http_response_code(302);
  header("location: " . autoUrl('admin/payments/products/new'));
}
