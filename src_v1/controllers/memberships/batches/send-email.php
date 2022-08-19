<?php

$user = app()->user;
$db = app()->db;

$getBatch = $db->prepare("SELECT membershipBatch.ID id, membershipBatch.Completed completed, DueDate due, Total total, PaymentTypes payMethods, PaymentDetails payDetails, membershipBatch.User `user` FROM membershipBatch INNER JOIN users ON users.UserID = membershipBatch.User WHERE membershipBatch.ID = ? AND users.Tenant = ?");
$getBatch->execute([
  $id,
  app()->tenant->getId(),
]);

$batch = $getBatch->fetch(PDO::FETCH_OBJ);

if (!$batch) halt(404);

if (!$user->hasPermission('Admin')) halt(404);

// Get batch items
$getBatchItems = $db->prepare("SELECT membershipBatchItems.ID id, membershipBatchItems.Membership membershipId, membershipBatchItems.Amount amount, membershipBatchItems.Notes notes, members.MForename firstName, members.MSurname lastName, members.ASANumber ngbId, clubMembershipClasses.Type membershipType, clubMembershipClasses.Name membershipName, clubMembershipClasses.Description membershipDescription, membershipYear.ID yearId, membershipYear.Name yearName, membershipYear.StartDate yearStart, membershipYear.EndDate yearEnd FROM membershipBatchItems INNER JOIN membershipYear ON membershipBatchItems.Year = membershipYear.ID INNER JOIN members ON members.MemberID = membershipBatchItems.Member INNER JOIN clubMembershipClasses ON clubMembershipClasses.ID = membershipBatchItems.Membership WHERE Batch = ?");
$getBatchItems->execute([
  $id
]);
$item = $getBatchItems->fetch(PDO::FETCH_OBJ);

$payMethods = json_decode($batch->payMethods);

$canPay = true;
$due = new DateTime($batch->due, new DateTimeZone('Europe/London'));
$due->setTime(0, 0, 0, 0);
$now = new DateTime('now', new DateTimeZone('Europe/London'));
$now->setTime(0, 0, 0, 0);
if ($now > $due) $canPay = false;

$batchUser = new User($batch->user);

$markdown = new \ParsedownExtra();
$markdown->setSafeMode(true);

$session = null;
$getSession = $db->prepare("SELECT `id` FROM `onboardingSessions` WHERE `batch` = ?");
$getSession->execute([
  $id,
]);
$sessionId = $getSession->fetchColumn();
if ($sessionId) $session = \SCDS\Onboarding\Session::retrieve($sessionId);

$message = '<p>There are membership fees for you to review in your club account.</p>';
$message .= '<p>Please <a href="' . htmlspecialchars(autoUrl("memberships/batches/$id")) . '">visit the membership system</a> to review the fees and pay ' . htmlspecialchars(MoneyHelpers::formatCurrency(MoneyHelpers::intToDecimal($batch->total), 'GBP')) . '.</p>';
$message .= '<p>' . htmlspecialchars(autoUrl("memberships/batches/$id")) . '</p>';
notifySend(null, 'New Memberships', $message, $batchUser->getFullName(), $batchUser->getEmail(), ['Name' => app()->tenant->getName() . ' Membership Secretary']);

$_SESSION['SentEmail'] = true;

header("location: " . autoUrl("memberships/batches/$id"));
