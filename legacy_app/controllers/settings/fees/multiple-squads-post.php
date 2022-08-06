<?php

try {

  if (!in_array(
    $_POST['fee-option'],
    ['Full']
  )) {
    throw new Exception();
  }

  tenant()->getLegacyTenant()->setKey('FeesWithMultipleSquads', $_POST['fee-option']);

  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Update-Success'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Update-Error'] = true;
}

header("Location: " . autoUrl("settings/fees/multiple-squads"));