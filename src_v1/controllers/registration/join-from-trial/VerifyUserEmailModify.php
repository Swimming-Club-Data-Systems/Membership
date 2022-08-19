<?php

$db = app()->db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash'], true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT Email FROM joinParents WHERE Hash = ?");
$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);

if ($query->fetchColumn() != $_POST['email-addr']) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['email-addr'] = $_POST['email-addr'];

  $code = random_int(100000, 999999);
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['EmailConfirmationCode'] = $code;

  $sub = "Verify your email address";
  $mes = '<p>Hi ' . $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['forename'] . ' ' . $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['surname'] . '.</p>
  <p>We noticed that you changed your email address to a different one from the one you used to register for a trial. Please enter the code shown below in the box on your screen.</p>
  <p>Your code is <strong>' . $code . '</strong></p>
  <p>Kind regards,<br>The ' . app()->tenant->getKey('CLUB_NAME') . ' Team</p>
  <p class="small text-muted">This email was sent to ' . $_POST['email-addr'] . '. If you did not expect this email, please ignore it.';

  notifySend(null, $sub, $mes, $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['forename'] . ' ' . $_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['surname'], $_POST['email-addr']);
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['EmailModified'] = true;
  header("Location: " . autoUrl("register/ac/verify-email"));
} else {
  header("Location: " . autoUrl("register/ac/terms-and-conditions"));
}
