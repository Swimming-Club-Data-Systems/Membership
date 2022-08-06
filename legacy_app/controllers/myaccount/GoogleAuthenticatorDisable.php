<?php

if (filter_var(getUserOption($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], "hasGoogleAuth2FA"), FILTER_VALIDATE_BOOLEAN)) {

  setUserOption($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], "hasGoogleAuth2FA", false);
  setUserOption($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'], "GoogleAuth2FASecret", null);
  header("Location: " . autoUrl("my-account/googleauthenticator"));

} else {
  halt(404);
}
