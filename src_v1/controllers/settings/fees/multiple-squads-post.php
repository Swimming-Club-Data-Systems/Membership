<?php

try {

  if (!in_array(
    $_POST['fee-option'],
    ['Full']
  )) {
    throw new Exception();
  }

  app()->tenant->setKey('FeesWithMultipleSquads', $_POST['fee-option']);

  $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error'] = true;
}

header("Location: " . autoUrl("settings/fees/multiple-squads"));