<?php

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$_SESSION['TENANT-' . app()->tenant->getId()] = null;
unset($_SESSION['TENANT-' . app()->tenant->getId()]);

$secure = true;
if (app('request')->protocol == 'http') {
  $secure = false;
}

setcookie(COOKIE_PREFIX . 'TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin', "", 0, "/", app('request')->hostname('request')->hostname, $secure, false);

if (isset($_COOKIE[COOKIE_PREFIX . 'TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin'])) {
  // Unset the hash.
  $db = app()->db;
  $unset = $db->prepare("UPDATE userLogins SET HashActive = ? WHERE Hash = ? AND UserID = ?");
  $unset->execute([
    0,
    $_COOKIE[COOKIE_PREFIX . 'TENANT-' . app()->tenant->getId() . '-' . 'AutoLogin'],
    $user
  ]);
}

session_destroy();
setcookie(COOKIE_PREFIX . 'TenantSessionId', "", 0, "/", app('request')->hostname('request')->hostname, $secure, false);

if (isset($_GET['redirect'])) {
  header("location: " . $_GET['redirect']);
} else {
  header("Location: " . autoUrl("", false));
}
