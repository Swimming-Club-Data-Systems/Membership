<?php

if (isset($_POST['CodeOfConduct'])) {
  try {
    
    app()->tenant->setKey('ParentCodeOfConduct', $_POST['CodeOfConduct']);
    $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED'] = true;
  } catch (Exception $e) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR'] = true;
  }
}

header("Location: " . autoUrl("settings/codes-of-conduct/parent"));