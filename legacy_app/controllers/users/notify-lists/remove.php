<?php

$db = nezamy_app()->db;
$tenant = nezamy_app()->tenant;

// Check user
$userCount = $db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ? AND Tenant = ?");
$userCount->execute([
  $id,
  $tenant->getId()
]);
if ($userCount->fetchColumn() == 0) {
  halt(404);
}

// Check list
$squadCount = $db->prepare("SELECT COUNT(*) FROM squads WHERE SquadID = ? AND Tenant = ?");
$squadCount->execute([
  $_GET['squad'],
  $tenant->getId()
]);
if ($squadCount->fetchColumn() == 0) {
  halt(404);
}

try {

  if (!isset($_GET['squad']) || $_GET['squad'] == null) {
    throw new Exception();
  }

  $insert = $db->prepare("DELETE FROM squadReps WHERE `User` = ? AND `Squad` = ?");
  $insert->execute([
    $id,
    $_GET['squad']
  ]);

  // Success
  $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['RemoveSquadSuccess'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['RemoveSquadError'] = true;
}

header("Location: " . autoUrl("users/" . $id . "/rep"));