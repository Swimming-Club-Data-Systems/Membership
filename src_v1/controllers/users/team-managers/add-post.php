<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!isset($_POST['gala-select']) || $_POST['gala-select'] == null) {
    throw new Exception();
  }

  // Check user
  $userCount = $db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ? AND Tenant = ?");
  $userCount->execute([
    $id,
    $tenant->getId()
  ]);
  if ($userCount->fetchColumn() == 0) {
    throw new Exception();
  }

  // Check squad
  $squadCount = $db->prepare("SELECT COUNT(*) FROM galas WHERE GalaID = ? AND Tenant = ?");
  $squadCount->execute([
    $_POST['gala-select'],
    $tenant->getId()
  ]);
  if ($squadCount->fetchColumn() == 0) {
    throw new Exception();
  }

  $insert = $db->prepare("INSERT INTO teamManagers (`User`, `Gala`) VALUES (?, ?)");
  $insert->execute([
    $id,
    $_POST['gala-select']
  ]);

  // Success
  $_SESSION['TENANT-' . app()->tenant->getId()]['AssignGalaSuccess'] = true;
  header("Location: " . autoUrl("users/" . $id . "/team-manager"));
} catch (Exception $e) {
  // Success
  reportError($e);
  $_SESSION['TENANT-' . app()->tenant->getId()]['AssignGalaError'] = true;
  header("Location: " . autoUrl("users/" . $id . "/team-manager/add"));
}