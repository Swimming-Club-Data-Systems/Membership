<?php

$db = app()->db;
use function GuzzleHttp\json_encode;

try {

  // Validate CSRF token
  if (!\SCDS\CSRF::verify()) throw new Exception('Invalid CSRF token');

  // Validate product exists
  $getCount = $db->prepare("SELECT COUNT(*) FROM `tenantPaymentProducts` WHERE `ID` = ?");
  $getCount->execute([
    $_POST['product'],
  ]);

  if ($getCount->fetchColumn() == 0) throw new Exception('No such product');

  $id = Ramsey\Uuid\Uuid::uuid4()->toString();

  $number = 1;
  if ($_POST['plan-frequency-n']) {
    $number = (int) $_POST['plan-frequency-n'];
  }
  if ($number == 0) throw new Exception('Invalid number for billing frequency');

  $type = 'M';
  switch ($_POST['plan-frequency-type']) {
    case 'days':
      $type = 'D';
      break;
    case 'weeks':
      $type = 'W';
      break;
    case 'months':
      $type = 'M';
      break;
    case 'years':
      $type = 'Y';
      break;
  }

  $billingInterval = 'P' . $number . $type;
  $price = \Brick\Math\BigDecimal::of((string) $_POST['plan-price'])->withPointMovedRight(2)->toInt();

  $insert = $db->prepare("INSERT INTO `tenantPaymentPlans` (`ID`, `Product`, `PricePerUnit`, `UsageType`, `Currency`, `BillingInterval`, `Name`) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $insert->execute([
    $id,
    $_POST['product'],
    $price,
    'recurring',
    'gbp',
    $billingInterval,
    $_POST['plan-name']
  ]);

  header('content-type: application/json');
  echo json_encode([
    'success' => true,
  ]);

} catch (Exception $e) {

  $message = $e->getMessage();
  if (get_class($e) == 'PDOException') {
    $message = 'A database error occurred';
  }

  header('content-type: application/json');
  echo json_encode([
    'success' => false,
    'error' => $message,
  ]);

}