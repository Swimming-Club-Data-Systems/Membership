<?php

function reportError($e) {
  $reportedError = false;
  if (getenv('ERROR_REPORTING_EMAIL') != null) {
    try {
      $emailMessage = '<p>This is an error report</p>';
      if (isset(nezamy_app()->tenant) && isset($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['UserID'])) {
        $emailMessage .= '<p>The active user was ' . htmlspecialchars($_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['Forename'] . ' ' . $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['Surname']) . ' (User ID #' . $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['UserID'] . ')</p>';
      }
      if (isset($e)) {
        ob_start();
        pre($e);
        $error = ob_get_clean();
        $emailMessage .= $error;
      }

      ob_start();
      pre(nezamy_app('request'));
      $error = ob_get_clean();
      $emailMessage .= $error;

      notifySend(null, 'System Error Report', $emailMessage, "System Admin", getenv('ERROR_REPORTING_EMAIL'));
      $reportedError = true;
    } catch (Exception $f) {
      $reportedError = false;
    }
  }

  return $reportedError;
}