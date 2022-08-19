<?php

use Ramsey\Uuid\Uuid;

if (!isset($_POST['id'])) halt(404);

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

$paymentTypes = [];
if (isset($_POST['payment-card']) && bool($_POST['payment-card'])) {
  $paymentTypes[] = 'card';
}
if (isset($_POST['payment-direct-debit']) && bool($_POST['payment-direct-debit'])) {
  $paymentTypes[] = 'dd';
}

// Update
$update = $db->prepare("UPDATE membershipBatch SET `PaymentTypes` = ? WHERE `ID` = ?");
$update->execute([
  json_encode($paymentTypes),
  $id,
]);

header('content-type: application/json');
echo json_encode([
  'success' => true,
]);
