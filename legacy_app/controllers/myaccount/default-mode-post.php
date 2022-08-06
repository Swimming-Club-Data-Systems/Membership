<?php

$fluidContainer = true;

$db = DB::connection()->getPdo();
$currentUser = app()->user;

$perms = $currentUser->getPrintPermissions();
$default = $currentUser->getUserOption('DefaultAccessLevel');

foreach ($perms as $key => $value) {
  if ($_POST['selector'] == $key) {
    $currentUser->setUserOption('DefaultAccessLevel', $key);
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['SavedChanges'] = true;
    break;
  }
}

header("location: " . autoUrl("my-account/default-access-level"));