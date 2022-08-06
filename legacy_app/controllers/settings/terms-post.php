<?php

try {
  

  if (isset($_POST['TermsAndConditions'])) {
    tenant()->getLegacyTenant()->setKey('TermsAndConditions', $_POST['TermsAndConditions']);
  }

  if (isset($_POST['PrivacyPolicy'])) {
    tenant()->getLegacyTenant()->setKey('PrivacyPolicy', $_POST['PrivacyPolicy']);
  }

  if (isset($_POST['WelcomeLetter'])) {
    tenant()->getLegacyTenant()->setKey('WelcomeLetter', $_POST['WelcomeLetter']);
  }

  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PCC-SAVED'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PCC-ERROR'] = true;
}

header("Location: " . autoUrl("settings/codes-of-conduct/terms-and-conditions"));