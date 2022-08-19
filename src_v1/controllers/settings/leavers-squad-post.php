<?php

if (isset($_POST['leavers-squad'])) {
  try {
    
    app()->tenant->setKey('LeaversSquad', $_POST['leavers-squad']);
    $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED'] = true;
  } catch (Exception $e) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR'] = true;
  }
}

header("Location: " . autoUrl("settings/leavers-squad"));