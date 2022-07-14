<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

// Validate and update user info

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberType;
use Respect\Validation\Validator as v;

// Check we have everything
$good = true;
$good = v::stringType()->length(1, null)->validate($_POST['first-name']) && $good;
$good = v::stringType()->length(1, null)->validate($_POST['last-name']) && $good;
$good = v::email()->validate($_POST['email-address']) && $good;
$good = v::phone()->validate($_POST['phone-number']) && $good;
$good = $_POST['password-1'] == $_POST['password-2'] && $good;
$good = !\CheckPwned::pwned($_POST['password-1']) && $good;

$phone = null;
if ($good) {
  try {
    $mobile = PhoneNumber::parse($_POST['phone-number'], 'GB');
    $phone = $mobile->format(PhoneNumberFormat::E164);
  } catch (Exception $e) {
    $good = false;
  }
}

if ($good) {
  // If all good, update the DB
  $update = $db->prepare("UPDATE `users` SET `Forename` = ?, `Surname` = ?, `EmailAddress` = ?, `Mobile` = ?, `Password` = ? WHERE `UserID` = ?");
  $update->execute([
    trim($_POST['first-name']),
    trim($_POST['last-name']),
    trim($_POST['email-address']),
    $phone,
    password_hash($_POST['password-1'], PASSWORD_ARGON2ID),
    $user->getId(),
  ]);

  // Set complete
  $session->completeTask('account_details');

  header('location: ' . autoUrl('onboarding/go'));
} else {
  $_SESSION['FormError'] = true;
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
