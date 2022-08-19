<?php

$db = app()->db;
$tenant = app()->tenant;

// Check user
$userCount = $db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ? AND Tenant = ?");
$userCount->execute([
  $id,
  $tenant->getId()
]);
if ($userCount->fetchColumn() == 0) {
  halt(404);
}

// Check squad
$squadCount = $db->prepare("SELECT COUNT(*) FROM squads WHERE SquadID = ? AND Tenant = ?");
$squadCount->execute([
  $_POST['squad-select'],
  $tenant->getId()
]);
if ($squadCount->fetchColumn() == 0) {
  halt(404);
}

try {

  if (!isset($_POST['squad-select']) || $_POST['squad-select'] == null) {
    throw new Exception();
  }

  $insert = $db->prepare("INSERT INTO squadReps (`User`, `Squad`) VALUES (?, ?)");
  $insert->execute([
    $id,
    $_POST['squad-select']
  ]);

  // Success
  $_SESSION['TENANT-' . app()->tenant->getId()]['AssignSquadSuccess'] = true;
  header("Location: " . autoUrl("users/" . $id . "/rep"));
} catch (Exception $e) {
  // Success
  $_SESSION['TENANT-' . app()->tenant->getId()]['AssignSquadError'] = true;
  header("Location: " . autoUrl("users/" . $id . "/rep/add"));
}