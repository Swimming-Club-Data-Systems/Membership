<?php

$user = $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'];

$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()] = null;
unset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]);

$secure = true;
if (app('request')->protocol == 'http') {
  $secure = false;
}

setcookie(COOKIE_PREFIX . 'TENANT-' . tenant()->getLegacyTenant()->getId() . '-' . 'AutoLogin', "", 0, "/", app('request')->hostname('request')->hostname, $secure, false);

if (isset($_COOKIE[COOKIE_PREFIX . 'TENANT-' . tenant()->getLegacyTenant()->getId() . '-' . 'AutoLogin'])) {
  // Unset the hash.
  $db = DB::connection()->getPdo();
  $unset = $db->prepare("UPDATE userLogins SET HashActive = ? WHERE Hash = ? AND UserID = ?");
  $unset->execute([
    0,
    $_COOKIE[COOKIE_PREFIX . 'TENANT-' . tenant()->getLegacyTenant()->getId() . '-' . 'AutoLogin'],
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
