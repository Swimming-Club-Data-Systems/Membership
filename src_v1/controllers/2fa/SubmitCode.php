<?php

$db = app()->db;
$tenant = app()->tenant;

$resetFailedLoginCount = $db->prepare("UPDATE users SET WrongPassCount = 0 WHERE UserID = ?");

use GeoIp2\Database\Reader;

$security_status = false;

use PragmaRX\Google2FA\Google2FA;

$ga2fa = new Google2FA();

if ($_POST['SessionSecurity'] == session_id()) {
  $security_status = true;
} else {
  $security_status = false;
}
if (SCDS\CSRF::verify()) {
  $security_status = true;
} else {
  $security_status = false;
}

$auth_via_google_authenticator;
try {
  $auth_via_google_authenticator = $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE'] && $ga2fa->verifyKey(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserID'], "GoogleAuth2FASecret"), $_POST['auth']);
} catch (Exception $e) {
  $auth_via_google_authenticator = false;
}

if (($_POST['auth'] == $_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_CODE']) || $auth_via_google_authenticator && $security_status) {
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR']);
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_CODE']);
  unset($_SESSION['TENANT-' . app()->tenant->getId()]['TWO_FACTOR_GOOGLE']);

  if ($auth_via_google_authenticator) {
    // Do work to prevent replay attacks etc.
  }

  try {
    $login = new \CLSASC\Membership\Login($db);
    $login->setUser($_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserID']);
    if (isset($_POST['RememberMe']) && bool($_POST['RememberMe'])) {
      $login->stayLoggedIn();
    }
    $currentUser = app()->user;
    $currentUser = $login->login();
    $resetFailedLoginCount->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['2FAUserID']]);

    $event = 'UserLogin-2FA-Email';
    if ($auth_via_google_authenticator) {
      $event = 'UserLogin-2FA-App';
    }
    AuditLog::new($event, 'Signed in from ' . getUserIp(), $currentUser->getId());
  } catch (Exception $e) {
    halt(403);
  }
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = true;
  if ($security_status == false) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = true;
    $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorStateLSVMessage'] = "We were unable to verify the integrity of your login attempt. The site you entered your username and password on may have been attempting to capture your login details. Try reseting your password urgently.";
    $_SESSION['TENANT-' . app()->tenant->getId()]['InfoSec'] = [$_POST['LoginSecurityValue'], $_SESSION['TENANT-' . app()->tenant->getId()]['LoginSec']];
  }
}

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) && bool(getUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "IsSpotCheck2FA"))) {
  setUserOption($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], "IsSpotCheck2FA", false);
}

unset($_SESSION['TENANT-' . app()->tenant->getId()]['LoginSec']);

if (isset($_POST['setup-time-based-codes']) && bool($_POST['setup-time-based-codes'])) {
  header("Location: " . autoUrl('my-account/googleauthenticator/setup'));
} else if (isset($_POST['target']) && $_POST['target']) {
  header("Location: " . autoUrl(ltrim($_POST['target'], '/'), false));
} else {
  header("Location: " . autoUrl(''));
}
