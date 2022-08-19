<?php

$db = app()->db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash'], true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$termsId = null;
try {
	$query = $db->prepare("SELECT ID FROM `posts` WHERE `Type` = ? LIMIT 1");
	$query->execute(['terms_conditions']);
  $termsId = $query->fetchColumn();
} catch (PDOException $e) {
	halt(500);
}

$query = $db->prepare("SELECT ID, First, Last FROM joinSwimmers WHERE Parent = ? AND SquadSuggestion IS NOT NULL ORDER BY First ASC, Last ASC");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);
$swimmers = $query->fetchAll();

$success = true;
$name = [];
foreach ($swimmers as $swimmer) {
  if ($_POST[$swimmer['ID'] . '-tc-confirm'] == "1") {
    // All good
  } else {
    $success = false;
    $name[] = htmlspecialchars($swimmer['First'] . ' ' . $swimmer['Last']);
  }
}

if ($success) {
  // Great news, move on the conduct codes
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] = 'CodeOfConduct';
  header("Location: " . autoUrl("register/ac/code-of-conduct"));
} else {
  // Parent must try again
  $selections = [];
  foreach ($_POST as $key => $check) {
    if ($check == "1") {
      $selections[$key] = " checked ";
    }
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-TC-Selected'] = $selections;
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-TC-ErrorNames'] = $name;
  header("Location: " . currentUrl());
}
