<?php

$fluidContainer = true;

$db = app()->db;
$currentUser = app()->user;

$perms = $currentUser->getPrintPermissions();
$default = $currentUser->getUserOption('DefaultAccessLevel');

foreach ($perms as $key => $value) {
  if ($_POST['selector'] == $key) {
    $currentUser->setUserOption('DefaultAccessLevel', $key);
    $_SESSION['TENANT-' . app()->tenant->getId()]['SavedChanges'] = true;
    break;
  }
}

header("location: " . autoUrl("my-account/default-access-level"));