<?php

$db = app()->db;
$tenant = app()->tenant;

$getKey = $db->prepare("SELECT SquadKey FROM squads WHERE SquadID = ? AND Tenant = ?");
$getKey->execute([
  $id,
  $tenant->getId()
]);
$squadDeleteKey = $getKey->fetchColumn();

if (mb_strlen(trim($_POST['squadName'])) == 0) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError'] = true;
}
if ($_POST['squadFee'] < 0) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateError'] = true;
}

if ($_POST['squadDeleteDanger'] ==  $squadDeleteKey) {
  // Delete the squad
  try {
    $delete = $db->prepare("DELETE FROM squads WHERE SquadID = ? AND Tenant = ?");
    $delete->execute([
      $id,
      $tenant->getId()
    ]);
  } catch (Exception $e) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateDatabaseError'] = true;
  }

  if ($_SESSION['TENANT-' . app()->tenant->getId()]['UpdateDatabaseError']) {
    header("Location: " . autoUrl("squads/" . $id . "/edit"));
  } else {
    $_SESSION['TENANT-' . app()->tenant->getId()]['DeleteSuccess'] = true;
    header("Location: " . autoUrl("squads"));
  }
} else {
  // Update the squad
  try {
    $update = $db->prepare("UPDATE squads SET SquadName = ?, SquadFee = ?, SquadCoach = ?, SquadTimetable = ?, SquadCoC = ? WHERE SquadID = ? AND Tenant = ?");
    $update->execute([
      trim($_POST['squadName']),
      $_POST['squadFee'],
      trim($_POST['squadCoach']),
      trim($_POST['squadTimetable']),
      trim($_POST['squadCoC']),
      $id,
      $tenant->getId()
    ]);
    $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateSuccess'] = true;
  } catch (Exception $e) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['UpdateDatabaseError'] = true;
  }
  header("Location: " . autoUrl("squads/" . $id . "/edit"));
}