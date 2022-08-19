<?php

$db = app()->db;

$club = null;
if (isset($_GET['club']) && mb_strlen((string) $_GET['club']) > 0) {
  $club = Tenant::fromCode((string) $_GET['club']);
  if (!$club) {
    $club = Tenant::fromId((int) $_GET['club']);
  }
}

app()->tenant = $club;

if (!$club || !isset($_SESSION['PassAuth-TENANT-' . $club->getId()]['User'])) {
  http_response_code(303);
  header("location: " . autoUrl("clubs"));
}

try {
  $login = new \CLSASC\Membership\Login($db);
  $login->setUser($_SESSION['PassAuth-TENANT-' . $club->getId()]['User']);
  if (isset($_GET['remember']) && bool($_GET['remember'])) {
    $login->stayLoggedIn();
  }
  $currentUser = $login->login();
  $resetFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = 0 WHERE UserID = ?");
  $resetFailedLoginCount->execute([$_SESSION['PassAuth-TENANT-' . $club->getId()]['User']]);

  $target = "";
  if (isset($_GET['target'])) {
    $target = ltrim($_GET['target'], '/');
  }

  header("location: " . autoUrl($target, false));

} catch (Exception $e) {
  http_response_code(303);
  header("location: " . autoUrl("clubs"));
}