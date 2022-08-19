<?php

if (!SCDS\CSRF::verify()) {
  halt(403);
}

$db = app()->db;
$tenant = app()->tenant;

use Respect\Validation\Validator as v;

$email = mb_strtolower(trim($_POST['new-user-email']));

if (!v::email()->validate($email)) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Error'] = true;
} else {
  // Update user email
  try {
    $update = $db->prepare("UPDATE users SET EmailAddress = ? WHERE UserID = ?");
    $update->execute([$email, $id]);
    $_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Success'] = true;
  } catch (Exception $e) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['User-Update-Email-Error'] = true;
  }
}

header("Location: " . autoUrl("users/" . $id));