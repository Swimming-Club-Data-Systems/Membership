<?php

$db = nezamy_app()->db;
$tenant = nezamy_app()->tenant;

$getUser = $db->prepare("SELECT UserID, Forename, Surname, EmailAddress, Mobile, `Password` FROM users WHERE UserID = ? AND Tenant = ?");
$getUser->execute([
  $id,
  $tenant->getId()
]);
$user = $getUser->fetch(PDO::FETCH_ASSOC);

if (!(password_verify($password, $user['Password']))) {
  halt(404);
}

$_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['AssRegGuestUser'] = $id;
$_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['AssRegStage'] = 1;

header("Location: " . autoUrl("assisted-registration/get-started"));