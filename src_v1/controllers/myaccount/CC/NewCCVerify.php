<?php

use Respect\Validation\Validator as v;

try {

  $email = mb_strtolower(trim($_POST['new-cc']));
  
  if (!v::email()->validate($email)) {
    throw new Exception();
  }

  $hash = hash('sha256', random_int(0, 999999999) . app()->tenant->getKey('CLUB_NAME'));
  $name = mb_ucfirst(trim($_POST['new-cc-name']));

  $db = app()->db;
  $insert = $db->prepare("INSERT INTO notifyAdditionalEmails (`UserID`, `EmailAddress`, `Name`, `Hash`) VALUES (?, ?, ?, ?)");
  $insert->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $email,
    $name,
    $hash
  ]);

  $id = $db->lastInsertId();

  $link = "verify-cc-email/auth/" . $id . "/" . $hash;

  $message = '
  <p>Hello ' . htmlspecialchars($name) . ',</p>
  <p>' . htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] . ' ' . $_SESSION['TENANT-' . app()->tenant->getId()]['Surname']) . ' wishes for you to also get emails from ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . '.</p>
  <p>Please follow the link below to verify your email address.</p>
  <p><strong><a href="' . autoUrl($link) . '">' . autoUrl($link) . '</a></strong></p>
  <p>This will confirm your email address and send carbon copies of emails from coaches and committee members at ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' to this address.</p>
  <p>If you did not request this, please ignore this email.</p>';

  if (!notifySend(null, "Verify your email", $message, $name, $email)) {
    throw new Exception();
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailSent'] = true;
  header("Location: " . autoUrl("my-account/email/cc/new"));
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailError'] = true;
  header("Location: " . autoUrl("my-account/email#cc"));
}