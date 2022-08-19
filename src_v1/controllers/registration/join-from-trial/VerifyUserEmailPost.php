<?php

if (trim($_POST['verify-code']) == $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['EmailConfirmationCode']) {
  // SUCCESS, continue to the terms and conditions
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['EmailConfirmationCode'] = "";
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['EmailConfirmationCode']);

  try {
    $db = app()->db;
    $query = $db->prepare("UPDATE joinParents SET Email = ? WHERE Hash = ?");
    $query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['AC-UserDetails']['email-addr'], $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Hash']]);

    $_SESSION['TENANT-' . app()->tenant->getId()]['AC-Registration']['Stage'] = 'TermsConditions';
    header("Location: " . autoUrl("register/ac/terms-and-conditions"));
  } catch (Exception $e) {
    halt(500);
  }
} else {
  // The code entered was wrong, try again
  $_SESSION['TENANT-' . app()->tenant->getId()]['AC-VerifyEmail-Error'] = true;
  header("Location: " . currentUrl());
}
