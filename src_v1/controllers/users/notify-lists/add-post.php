<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!isset($_POST['list-select']) || $_POST['list-select'] == null) {
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

  // Check list
  $listCount = $db->prepare("SELECT COUNT(*) FROM targetedLists WHERE ID = ? AND Tenant = ?");
  $listCount->execute([
    $_POST['list-select'],
    $tenant->getId()
  ]);
  if ($listCount->fetchColumn() == 0) {
    throw new Exception();
  }

  $insert = $db->prepare("INSERT INTO listSenders (`User`, `List`, `Manager`) VALUES (?, ?, ?)");
  $insert->execute([
    $id,
    $_POST['list-select'],
    0,
  ]);

  // Success
  $_SESSION['TENANT-' . app()->tenant->getId()]['AssignListSuccess'] = true;
  header("Location: " . autoUrl("users/" . $id . "/targeted-lists"));
} catch (Exception $e) {
  // Error
  
  reportError($e);
  
  $_SESSION['TENANT-' . app()->tenant->getId()]['AssignListError'] = true;
  header("Location: " . autoUrl("users/" . $id . "/targeted-lists/add"));
}
