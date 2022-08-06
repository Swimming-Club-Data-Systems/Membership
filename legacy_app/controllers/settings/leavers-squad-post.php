<?php

if (isset($_POST['leavers-squad'])) {
  try {
    
    tenant()->getLegacyTenant()->setKey('LeaversSquad', $_POST['leavers-squad']);
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PCC-SAVED'] = true;
  } catch (Exception $e) {
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PCC-ERROR'] = true;
  }
}

header("Location: " . autoUrl("settings/leavers-squad"));