<?php

$db = app()->db;

if ($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE']) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_CODE'] = random_int(100000, 999999);
  $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE'] = false;
}

try {
  $query = $db->prepare("SELECT EmailAddress, Forename, Surname FROM users WHERE UserID = ?");
  $query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserID']]);
  $row = $query->fetch(PDO::FETCH_ASSOC);

  $date = new DateTime('now', new DateTimeZone('Europe/London'));

  $message = '
  <p>Hello. Confirm your login by entering the following code in your web browser.</p>
  <p><strong>' . $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_CODE'] . '</strong></p>
  <p>If you did not just try to log in, you can ignore this email. You may want to reset your password.</p>
  <p>This email was resent to this address at the request of the user.</p>
  <p>Kind Regards, <br>The ' . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . ' Team</p>';

  if (notifySend(null, "Verification Code - Requested at " . $date->format("H:i:s \o\\n d/m/Y"), $message, $row['Forename'] . " " . $row['Surname'], $row['EmailAddress'])) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR'] = true;
    $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_RESEND'] = true;
    header("Location: " . autoUrl("2fa"));
  } else {
    halt(500);
  }
} catch (Exception $e) {
  halt(500);
}
