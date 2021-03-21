<?php

$db = nezamy_app()->db;

try {

  $exemptMonths = [];

  for ($m = 1; $m <= 12; $m++) {
    $month =  mktime(0, 0, 0, $m, 1);
    if (isset($_POST['month-' . date('m', $month)]) && bool($_POST['month-' . date('m', $month)])) {
      $exemptMonths += [date('m', $month) => true];
    } else {
      $exemptMonths += [date('m', $month) => false];
    }
  }

  nezamy_app()->tenant->setKey('SquadFeeMonths', json_encode($exemptMonths));
  $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['Update-Success'] = true;
} catch (Exception $e) {
  $_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['Update-Error'] = true;
}

header("Location: " . autoUrl("settings/fees/charge-months"));