<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF token');
  }

  $getClass = $db->prepare("SELECT `ID`, `Name`, `Description`, `Fees` FROM `clubMembershipClasses` WHERE `ID` = ? AND `Tenant` = ? AND `Type` = ?");

  if (isset($_POST['default-class'])) {
    // Validate
    $getClass->execute([
      $_POST['default-class'],
      $tenant->getId(),
      'club'
    ]);
    $class = $getClass->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
      throw new Exception('Club membership class not found at this tenant');
    }

    $tenant->setKey('DEFAULT_MEMBERSHIP_CLASS', $_POST['default-class']);
  }

  if (isset($_POST['default-ngb-class'])) {
    // Validate
    $getClass->execute([
      $_POST['default-ngb-class'],
      $tenant->getId(),
      'national_governing_body'
    ]);
    $class = $getClass->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
      throw new Exception('National governing body membership class not found at this tenant');
    }

    $tenant->setKey('DEFAULT_NGB_MEMBERSHIP_CLASS', $_POST['default-ngb-class']);
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error'] = true;
}

http_response_code(302);
header('location: ' . autoUrl('settings/fees/membership-fees'));
