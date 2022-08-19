<?php

use Ramsey\Uuid\Uuid;

if (!isset($_POST['id']) || !isset($_POST['member'])) halt(404);

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

// Get members for this user
$getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM members WHERE UserID = ? AND MemberID = ? AND Active ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $batch->user,
  $_POST['member']
]);

$member = $getMembers->fetch(PDO::FETCH_OBJ);

// Validate year
$getYears = $db->prepare("SELECT ID FROM `membershipYear` WHERE `Tenant` = ? AND `ID` = ?");
$getYears->execute([
  $tenant->getId(),
  $_POST['membership-year'],
]);
$year = $getYears->fetchColumn();
if (!$year) throw new Exception('Invalid membership year');

// Work out available memberships
$getMemberships = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description`, `Fees` `fees`, `Type` `type` FROM `clubMembershipClasses` WHERE `Tenant` = ? AND `ID` NOT IN (SELECT `Membership` AS `ID` FROM `memberships` WHERE `Member` = ? AND `Year` = ?) AND `ID` NOT IN (SELECT `Membership` AS `ID` FROM `membershipBatchItems` INNER JOIN membershipBatch ON membershipBatchItems.Batch = membershipBatch.ID WHERE `Member` = ? AND `membershipBatch`.`ID` = ?)");
$getMemberships->execute([
  $tenant->getId(),
  $_POST['member'],
  $year,
  $_POST['member'],
  $batch->id,
]);

$allowed = false;
while ($membership = $getMemberships->fetch(PDO::FETCH_OBJ)) {
  if ($_POST['membership'] == $membership->id) $allowed = true;
}

$success = false;

if ($allowed) {
  // Add batch item
  $amount = (int) MoneyHelpers::decimalToInt($_POST['membership-amount']);

  if ($amount < 0) throw new Exception('Negative number');

  $addBatchItem = $db->prepare("INSERT INTO `membershipBatchItems` (`ID`, `Batch`, `Membership`, `Member`, `Amount`, `Notes`, `Year`) VALUES (?, ?, ?, ?, ?, ?, ?);");
  $addBatchItem->execute(
    [
      Uuid::uuid4(),
      $batch->id,
      $_POST['membership'],
      $_POST['member'],
      $amount,
      trim($_POST['membership-notes']),
      $year
    ]
  );

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
}

$html = "";

// reportError(htmlentities($html));

header('content-type: application/json');
echo json_encode([
  'html' => $html,
  'success' => $success,
]);
