<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$session = \SCDS\Onboarding\Session::retrieve($id);

use Ramsey\Uuid\Uuid;
use Respect\Validation\Exceptions\NegativeException;

$db = app()->db;
$tenant = app()->tenant;

$getYear = $db->prepare("SELECT `Name`, `StartDate`, `EndDate` FROM `membershipYear` WHERE `ID` = ? AND `Tenant` = ?");
$getYear->execute([
  $_POST['year'],
  $tenant->getId(),
]);
$year = $getYear->fetch(PDO::FETCH_ASSOC);

if (!$year) halt(404);

if (!isset($session->user)) halt(404);

// Check user exists
$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $session->user
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$getMembers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, NGBCategory ngb, ngbMembership.Name ngbName, ngbMembership.Fees ngbFees, ClubCategory club, clubMembership.Name clubName, clubMembership.Fees clubFees FROM members INNER JOIN clubMembershipClasses AS ngbMembership ON ngbMembership.ID = members.NGBCategory INNER JOIN clubMembershipClasses AS clubMembership ON clubMembership.ID = members.ClubCategory INNER JOIN onboardingMembers ON onboardingMembers.member = members.MemberID WHERE Active AND onboardingMembers.session = ? ORDER BY fn ASC, sn ASC");
$getMembers->execute([
  $session->id
]);
$member = $getMembers->fetch(PDO::FETCH_OBJ);

$getCurrentMemberships = $db->prepare("SELECT `Name` `name`, `Description` `description`, `Type` `type`, `memberships`.`Amount` `paid`, `clubMembershipClasses`.`Fees` `expectPaid` FROM `memberships` INNER JOIN clubMembershipClasses ON memberships.Membership = clubMembershipClasses.ID WHERE `Member` = ? AND `Year` = ?");
$hasMembership = $db->prepare("SELECT COUNT(*) FROM memberships WHERE `Member` = ? AND `Year` = ? AND `Membership` = ?");

try {

  $items = [];
  $total = 0;
  $batchId = Uuid::uuid4();

  while ($member = $getMembers->fetch(PDO::FETCH_OBJ)) {
    $hasMembership->execute([
      $member->id,
      $_POST['year'],
      $member->ngb,
    ]);
    $has = $hasMembership->fetchColumn() > 0;

    if (!$has && isset($_POST[$member->id . '-' . $member->ngb . '-yes'])) {
      $amount = (int) MoneyHelpers::decimalToInt($_POST[$member->id . '-' . $member->ngb . '-amount']);

      if ($amount < 0) throw new Exception('Negative number');

      $total += $amount;

      $items[] = [
        Uuid::uuid4(),
        $batchId,
        $member->ngb,
        $member->id,
        $amount,
        trim($_POST[$member->id . '-' . $member->ngb . '-notes']),
      ];
    }

    $hasMembership->execute([
      $member->id,
      $_POST['year'],
      $member->club,
    ]);
    $has = $hasMembership->fetchColumn() > 0;

    if (!$has && isset($_POST[$member->id . '-' . $member->club . '-yes'])) {
      $amount = (int) MoneyHelpers::decimalToInt($_POST[$member->id . '-' . $member->club . '-amount']);

      if ($amount < 0) throw new Exception('Negative number');

      $total += $amount;

      $items[] = [
        Uuid::uuid4(),
        $batchId,
        $member->club,
        $member->id,
        $amount,
        trim($_POST[$member->id . '-' . $member->club . '-notes']),
      ];
    }
  }

  // Add batch info

  $dueDate = null;

  $paymentTypes = [];
  if (isset($_POST['payment-card']) && bool($_POST['payment-card'])) {
    $paymentTypes[] = 'card';
  }
  if (isset($_POST['payment-direct-debit']) && bool($_POST['payment-direct-debit'])) {
    $paymentTypes[] = 'dd';
  }

  $addBatch = $db->prepare("INSERT INTO `membershipBatch` (`ID`, `User`, `Year`, `StartText`, `Footer`, `DueDate`, `Total`, `PaymentTypes`, `PaymentDetails`, `AutoReminders`, `Creator`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");

  $addBatch->execute([
    $batchId,
    $session->user,
    $_POST['year'],
    null,
    null,
    $dueDate,
    $total,
    json_encode($paymentTypes),
    json_encode([]),
    0,
    app()->user->getId(),
  ]);

  $addBatchItem = $db->prepare("INSERT INTO `membershipBatchItems` (`ID`, `Batch`, `Membership`, `Member`, `Amount`, `Notes`) VALUES (?, ?, ?, ?, ?, ?);");

  foreach ($items as $item) {
    $addBatchItem->execute($item);
  }

  // $message = '<p>There are membership fees for you to review in your club account.</p>';
  // $message .= '<p>Please <a href="' . htmlspecialchars(autoUrl("memberships/batches/$batchId")) . '">visit the membership system</a> to review the fees and pay ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($total), 'GBP')) . '.</p>';
  // $message .= '<p>' . htmlspecialchars(autoUrl("memberships/batches/$batchId")) . '</p>';
  // notifySend(null, 'New Memberships', $message, $info['Forename'] . ' ' . $info['Surname'], $info['EmailAddress'], ['Name' => app()->tenant->getName() . ' Membership Secretary']);

  // Add batch ID to session

  $update = $db->prepare("UPDATE `onboardingSessions` SET `batch` = ? WHERE `id` = ?");
  $update->execute([
    $batchId,
    $id,
  ]);

  http_response_code(302);
  header("location: " . autoUrl("onboarding/sessions/a/$id"));
} catch (Exception $e) {
  reportError($e);

  http_response_code(302);
  header("location: " . autoUrl("onboarding/sessions/a/$id/batch?year=" . $_POST['year']));
}
