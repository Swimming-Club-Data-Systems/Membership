<?php

use Ramsey\Uuid\Uuid;

if (!isset($_POST['id']) || !isset($_POST['item-id'])) halt(404);

$id = $_POST['id'];

$user = app()->user;
$db = app()->db;
$tenant = app()->tenant;

$getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails, membershipBatch.User `user` FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
$getBatch->execute([
  $id,
  app()->tenant->getId(),
]);

$batch = $getBatch->fetch(PDO::FETCH_OBJ);

if (!$batch) halt(404);

if (!$user->hasPermission('Admin')) halt(404);

// Get batch item
$getItem = $db->prepare("SELECT COUNT(*) FROM membershipBatchItems WHERE `Batch` = ? AND `ID` = ?");
$getItem->execute([
  $batch->id,
  $_POST['item-id'],
]);

if ($getItem->fetchColumn() == 0) halt(404);

// Validate year
$getYears = $db->prepare("SELECT ID FROM `membershipYear` WHERE `Tenant` = ? AND `ID` = ?");
$getYears->execute([
  $tenant->getId(),
  $_POST['membership-year'],
]);
$year = $getYears->fetchColumn();
if (!$year) throw new Exception('Invalid membership year');

$success = false;

// Update batch item
$amount = (int) MoneyHelpers::decimalToInt($_POST['amount']);

if ($amount < 0) throw new Exception('Negative number');

$addBatchItem = $db->prepare("UPDATE `membershipBatchItems` SET `Amount` = ?, `Notes` = ?, `Year` = ? WHERE `ID` = ?;");
$addBatchItem->execute([
  $amount,
  trim($_POST['notes']),
  $year,
  $_POST['item-id'],
]);

// Recalculate batch total
$batchTotal = $db->prepare("SELECT SUM(`Amount`) FROM `membershipBatchItems` WHERE `Batch` = ?");
$batchTotal->execute([
  $batch->id,
]);
$total = $batchTotal->fetchColumn();

// Update batch total
$updateBatch = $db->prepare("UPDATE `membershipBatch` SET `Total` = ? WHERE `ID` = ?");
$updateBatch->execute([
  $total,
  $batch->id,
]);

$success = true;

$html = "";

// reportError(htmlentities($html));

header('content-type: application/json');
echo json_encode([
  'success' => $success,
]);
