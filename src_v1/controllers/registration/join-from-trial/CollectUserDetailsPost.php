<?php

use Respect\Validation\Validator as v;

$db = app()->db;

$_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData'] = $_POST;

// Overwrite passwords so they aren't kept in the session file
$_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData']['password1'] = "";
$_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData']['password2'] = "";

$errorState = false;

if (!v::email()->validate($_POST['email-addr'])) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-RegUserDetails-Errors']['Email'] = "The email address is invalid";
  $errorState = true;
}

if ($_POST['forename'] == "") {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-RegUserDetails-Errors']['Parent-FN'] = "No parent first name";
  $errorState = true;
}

if ($_POST['surname'] == "") {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-RegUserDetails-Errors']['Parent-LN'] = "No parent last name";
  $errorState = true;
}

if ($_POST['password1'] != $_POST['password2']) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-RegUserDetails-Errors']['PasswordMatch'] = "Passwords do not match";
  $errorState = true;
}

if (!v::stringType()->length(7, null)->validate($_POST['password1'])) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-RegUserDetails-Errors']['PasswordMatch'] = "Password does not meet the minimum length requirements";
  $errorState = true;
}

if ($_POST['mobile'] == "") {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-RegUserDetails-Errors']['MobileMissing'] = "You didn't provide a mobile number";
  $errorState = true;
}

$_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData']['mobile'] = "+44" . ltrim(preg_replace('/\D/', '', str_replace("+44", "", $_POST['mobile'])), '0');

// If errors are set, kill
if ($errorState) {
  header("Location: " . currentUrl());
  die();
}

// Otherwise begin to continue. If email submitted is different to original, it
// must be verified.

$_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails'] = $_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData'];

// Hash the user password so it is not kept as plain text in the session
$_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['password-hash'] = password_hash($_POST['password1'], PASSWORD_BCRYPT);

$query = $db->prepare("SELECT Email FROM joinParents WHERE Hash = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);

if ($query->fetchColumn() != $_POST['email-addr']) {
  $code = random_int(100000, 999999);
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['EmailConfirmationCode'] = $code;

  $sub = "Verify your email address";
  $mes = '<p>Hi ' . $_POST['forename'] . ' ' . $_POST['surname'] . '.</p>
  <p>We noticed that you changed your email address to a different one from the one you used to register for a trial. Please enter the code shown below in the box on your screen.</p>
  <p>Your code is <strong>' . $code . '</strong></p>
  <p>Kind regards,<br>The ' . app()->tenant->getKey('CLUB_NAME') . ' Team</p>';

  notifySend(null, $sub, $mes, $_POST['forename'] . ' ' . $_POST['surname'], $_POST['email-addr']);
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] = 'VerifyEmail';
  header("Location: " . autoUrl("register/ac/verify-email"));
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] = 'TermsConditions';
  header("Location: " . autoUrl("register/ac/terms-and-conditions"));
}

unset($_SESSION['TENANT-' . app()->tenant->getId()]['UserDetailsPostData']);
