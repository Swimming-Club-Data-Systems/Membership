<?php

$db = app()->db;
$tenant = app()->tenant;

// Check gala
$getGalas = $db->prepare("SELECT COUNT(*) FROM galas INNER JOIN galaEntries ON galas.GalaID = galaEntries.GalaID WHERE galaEntries.EntryID = ? AND galas.Tenant = ?");
$getGalas->execute([
  $_POST['entry'],
  $tenant->getId()
]);
if ($getGalas->fetchColumn() == 0) {
  halt(404);
}

// Get entry from post data and check if user is a rep for the swimmer's squad
// Non-Parents have auto permission

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $getSquad = $db->prepare("SELECT SquadID FROM galaEntries INNER JOIN members ON members.MemberID = galaEntries.MemberID WHERE EntryID = ?");
  $getSquad->execute([
    $_POST['entry']
  ]);
  $squad = $getSquad->fetchColumn();
  if ($squad == null) {
    halt(404);
  }

  $getRepPermission = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE `Squad` = ? AND `User` = ?");
  $getRepPermission->execute([
    (int) $squad,
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
  if ($getRepPermission->fetchColumn() == 0) {
    halt(404);
  }
}

// Now decide whether what to update

if ($_POST['event'] == 'mark-paid') {
  // Mark entry as paid

  try {
    $markPaid = $db->prepare("UPDATE galaEntries SET Charged = ? WHERE EntryID = ?");
    $markPaid->bindValue (1, bool($_POST['state']), PDO::PARAM_BOOL);
    $markPaid->bindValue (2, $_POST['entry'], PDO::PARAM_INT);
    $markPaid->execute();
    echo 'success';
  } catch (Exception $e) {
    reportError($e);
  }

} else if ($_POST['event'] == 'approve-entry') {
  // Mark as approved

  try {
    $markApproved = $db->prepare("UPDATE galaEntries SET Approved = ? WHERE EntryID = ?");
    $markApproved->bindValue (1, bool($_POST['state']), PDO::PARAM_BOOL);
    $markApproved->bindValue (2, $_POST['entry'], PDO::PARAM_INT);
    $markApproved->execute();
    echo 'success';
  } catch (Exception $e) {
    reportError($e);
  }
}