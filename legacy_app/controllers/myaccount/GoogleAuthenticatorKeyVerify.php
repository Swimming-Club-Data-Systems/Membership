<?php

$setup = false;
$secretKey = null;

use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();
$success = $google2fa->verifyKey($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['G2FAKey'], $_POST['verify']);

if ($success) {
  setUserOption($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], "hasGoogleAuth2FA", true);
  setUserOption($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], "GoogleAuth2FASecret", $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['G2FAKey']);
  unset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['G2FAKey']);
  header("Location: " . autoUrl("my-account/googleauthenticator"));
} else {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['G2FA_VerifyError'] = true;
  header("Location: " . autoUrl("my-account/googleauthenticator/setup"));
}
