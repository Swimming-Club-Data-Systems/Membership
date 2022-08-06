<?php

if (!SCDS\CSRF::verify()) {
  halt(403);
}

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

use Respect\Validation\Validator as v;

$email = mb_strtolower(trim($_POST['new-user-email']));

if (!v::email()->validate($email)) {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['User-Update-Email-Error'] = true;
} else {
  // Update user email
  try {
    $update = $db->prepare("UPDATE users SET EmailAddress = ? WHERE UserID = ?");
    $update->execute([$email, $id]);
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['User-Update-Email-Success'] = true;
  } catch (Exception $e) {
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['User-Update-Email-Error'] = true;
  }
}

header("Location: " . autoUrl("users/" . $id));