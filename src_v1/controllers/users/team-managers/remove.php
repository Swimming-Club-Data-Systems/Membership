<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!isset($_GET['gala']) || $_GET['gala'] == null) {
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
    $_GET['gala'],
    $tenant->getId()
  ]);
  if ($squadCount->fetchColumn() == 0) {
    throw new Exception();
  }

  $insert = $db->prepare("DELETE FROM teamManagers WHERE `User` = ? AND `Gala` = ?");
  $insert->execute([
    $id,
    $_GET['gala']
  ]);

  // Success
  $_SESSION['TENANT-' . app()->tenant->getId()]['RemoveGalaSuccess'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RemoveGalaError'] = true;
}

header("Location: " . autoUrl("users/" . $id . "/team-manager"));