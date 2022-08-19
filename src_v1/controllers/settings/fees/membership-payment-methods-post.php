<?php

try {

  $names = [
    'MEMBERSHIP_FEE_PM_CARD',
    'MEMBERSHIP_FEE_PM_DD',
    // 'MEMBERSHIP_FEE_PM_CASH',
    // 'MEMBERSHIP_FEE_PM_CHEQUE',
    // 'MEMBERSHIP_FEE_PM_BACS',
  ];

  foreach ($names as $key => $value) {
    $hide = 1;
    if (!isset($_POST[$value]) || !bool($_POST[$value])) {
      $hide = 0;
    }
    app()->tenant->setKey($value, $hide);
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Success'] = true;
} catch (Exception $e) {

  $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error'] = true;
}

http_response_code(302);
header("location: " . autoUrl('settings/fees/membership-fee-payment-methods'));
