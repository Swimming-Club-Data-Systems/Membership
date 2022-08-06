<?php

if (isset($_POST['CodeOfConduct'])) {
  try {
    
    tenant()->getLegacyTenant()->setKey('ParentCodeOfConduct', $_POST['CodeOfConduct']);
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PCC-SAVED'] = true;
  } catch (Exception $e) {
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PCC-ERROR'] = true;
  }
}

header("Location: " . autoUrl("settings/codes-of-conduct/parent"));