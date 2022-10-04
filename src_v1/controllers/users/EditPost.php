<?php

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Respect\Validation\Validator as v;

$db = app()->db;
$tenant = app()->tenant;

$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile FROM users WHERE UserID = ? AND Tenant = ?");
$userInfo->execute([
  $id,
  $tenant->getId()
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

try {

  $email = trim(mb_convert_case($_POST['email-address'], MB_CASE_LOWER));
  $mobile = trim($_POST['mobile-phone']);

  if (!v::email()->validate($email)) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['InvalidEmail'] = true;
    throw new Exception();
  }

  $get = $db->prepare("SELECT COUNT(*) FROM users WHERE EmailAddress = ? AND UserID != ? AND Tenant = ?");
  $get->execute([
    $email,
    $id,
    $tenant->getId(),
  ]);

  if ($get->fetchColumn() > 0) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['UsedEmail'] = true;
    throw new Exception();
  }

  try {
    $mobile = PhoneNumber::parse($mobile, 'GB');
    $mobile = $mobile->format(PhoneNumberFormat::E164);
  } catch (PhoneNumberParseException $e) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['InvalidPhone'] = true;
    throw new Exception();
  }

  $update = $db->prepare("UPDATE users SET Forename = ?, Surname = ?, EmailAddress = ?, Mobile = ? WHERE UserID = ?");
  $update->execute([
    trim(mb_convert_case($_POST['first-name'], MB_CASE_TITLE_SIMPLE)),
    trim(mb_convert_case($_POST['last-name'], MB_CASE_TITLE_SIMPLE)),
    $email,
    $mobile,
    $id,
  ]);

  // Set access permissions
  $baseRequired = true;
  $userObject = new \User($id);

  // Galas
  if (bool($_POST['permissions-gala'])) {
    $userObject->grantPermission('Galas');
    $baseRequired = false;
  } else {
    $userObject->revokePermission('Galas');
  }

  // Coach
  if (bool($_POST['permissions-coach'])) {
    $userObject->grantPermission('Coach');
    $baseRequired = false;
  } else {
    $userObject->revokePermission('Coach');
  }

  // Admin
  if (bool($_POST['permissions-admin'])) {
    $userObject->grantPermission('Admin');
    $baseRequired = false;
  } else {
    $userObject->revokePermission('Admin');
  }

  // SCDS Payments Access
  if (bool($_POST['permissions-scds-payments-manager'])) {
    $userObject->grantPermission('SCDSPaymentsManager');
    $userObject->grantPermission('Admin');
    $baseRequired = false;
  } else {
    $userObject->revokePermission('SCDSPaymentsManager');
  }

  // Parent
  if (bool($_POST['permissions-parent']) || $baseRequired) {
    $userObject->grantPermission('Parent');
  } else {
    $userObject->revokePermission('Parent');
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['Success'] = true;
} catch (Exception $e) {
  // reportError($e);
  $_SESSION['TENANT-' . app()->tenant->getId()]['GeneralError'] = true;
}

header("Location: " . autoUrl("users/" . $id . "/edit"));
