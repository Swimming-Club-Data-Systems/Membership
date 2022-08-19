<?php

if (!app()->user->hasPermission('Admin')) halt(404);

use Ramsey\Uuid\Uuid;
use Respect\Validation\Exceptions\NegativeException;

$db = app()->db;
$tenant = app()->tenant;

if (!isset($_GET['user'])) halt(404);

// Check user exists
$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $_GET['user']
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$getMembers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, NGBCategory ngb, ngbMembership.Name ngbName, ngbMembership.Fees ngbFees, ClubCategory club, clubMembership.Name clubName, clubMembership.Fees clubFees FROM members INNER JOIN clubMembershipClasses AS ngbMembership ON ngbMembership.ID = members.NGBCategory INNER JOIN clubMembershipClasses AS clubMembership ON clubMembership.ID = members.ClubCategory WHERE Active AND UserID = ? ORDER BY fn ASC, sn ASC");
$getMembers->execute([
  $_GET['user']
]);

try {

  $total = 0;
  $batchId = Uuid::uuid4();

  // Add batch info

  $dueDate = null;
  if (isset($_POST['due-date'])) {
    try {
      $dueDate = new DateTime($_POST['due-date'], new DateTimeZone('Europe/London'));
      $dueDate = $dueDate->format('Y-m-d');
    } catch (Exception $e) {
      // Ignore
      $dueDate = null;
    }
  }

  $paymentTypes = [];
  if (isset($_POST['payment-card']) && bool($_POST['payment-card'])) {
    $paymentTypes[] = 'card';
  }
  if (isset($_POST['payment-direct-debit']) && bool($_POST['payment-direct-debit'])) {
    $paymentTypes[] = 'dd';
  }

  $addBatch = $db->prepare("INSERT INTO `membershipBatch` (`ID`, `User`, `StartText`, `Footer`, `DueDate`, `Total`, `PaymentTypes`, `PaymentDetails`, `AutoReminders`, `Creator`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");

  $addBatch->execute([
    $batchId,
    $_GET['user'],
    trim($_POST['introduction-text']),
    trim($_POST['footer-text']),
    $dueDate,
    $total,
    json_encode($paymentTypes),
    json_encode([]),
    0,
    app()->user->getId(),
  ]);

  // $message = '<p>There are membership fees for you to review in your club account.</p>';
  // $message .= '<p>Please <a href="' . htmlspecialchars(autoUrl("memberships/batches/$batchId")) . '">visit the membership system</a> to review the fees and pay ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($total), 'GBP')) . '.</p>';
  // $message .= '<p>' . htmlspecialchars(autoUrl("memberships/batches/$batchId")) . '</p>';
  // notifySend(null, 'New Memberships', $message, $info['Forename'] . ' ' . $info['Surname'], $info['EmailAddress'], ['Name' => app()->tenant->getName() . ' Membership Secretary']);

  http_response_code(302);
  header("location: " . autoUrl("memberships/batches/$batchId/edit"));
} catch (Exception $e) {
  reportError($e);

  http_response_code(302);
  header("location: " . autoUrl("memberships/batches/new?user=" . $_GET['user']));
}
