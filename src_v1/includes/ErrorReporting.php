<?php

function reportError($e)
{
  $reportedError = false;
  if (getenv('ERROR_REPORTING_EMAIL') != null) {
    try {
      $emailMessage = '<p>This is an error report</p>';
      if (isset(app()->tenant) && isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
        $emailMessage .= '<p>The active user was ' . htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] . ' ' . $_SESSION['TENANT-' . app()->tenant->getId()]['Surname']) . ' (User ID #' . $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] . ')</p>';
      }

      $emailMessage .= '<p><strong>Path:</strong> ' . htmlspecialchars(app('request')->path) . '</p>';
      $emailMessage .= '<p><strong>Host:</strong> ' . htmlspecialchars(app('request')->hostname) . '</p>';
      $emailMessage .= '<p><strong>Router Browser:</strong> ' . htmlspecialchars(app('request')->browser()) . '</p>';
      $emailMessage .= '<p><strong>Router Platform:</strong> ' . htmlspecialchars(app('request')->platform()) . '</p>';
      $emailMessage .= '<p><strong>IP:</strong> ' . htmlspecialchars(getUserIp()) . '</p>';
      if (isset($_SERVER["HTTP_CF_IPCOUNTRY"])) {
        $emailMessage .= '<p><strong>Cloudflare:</strong> YES</p>';
        $emailMessage .= '<p><strong>Country Code:</strong> ' . htmlspecialchars($_SERVER["HTTP_CF_IPCOUNTRY"]) . '</p>';
      } else {
        $emailMessage .= '<p><strong>Cloudflare:</strong> NO</p>';
      }
      $emailMessage .= '<p><strong>Locale:</strong> ' . htmlspecialchars(app()->locale) . '</p>';

      try {
        $browser = new \WhichBrowser\Parser(getallheaders());

        $emailMessage .= '<p><strong>Sniffed Browser/Platform:</strong> ' . htmlspecialchars($browser->toString()) . '</p>';
      } catch (Exception $e) {
        $emailMessage .= '<p><strong>U/A Info:</strong> Not Available</p>';
      }

      if (isset($e)) {
        ob_start();
        pre($e);
        $error = ob_get_clean();
        $emailMessage .= $error;
      }

      notifySend(null, 'System Error Report', $emailMessage, "System Admin", getenv('ERROR_REPORTING_EMAIL'));
      $reportedError = true;
    } catch (Exception $f) {
      $reportedError = false;
    }
  }

  return $reportedError;
}
