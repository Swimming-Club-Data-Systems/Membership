<?php

$db = DB::connection()->getPdo();

setUserOption($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], 'DefaultAccessLevel', $_GET['type']);
// $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['SelectedAccessLevel'] = $_GET['type'];

$userObject = new \User($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], true);

if (isset($_GET['redirect'])) {
  header("location: " . urldecode($_GET['redirect']));
} else {
  header("location: " . autoUrl(""));
}
