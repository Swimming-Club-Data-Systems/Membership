<?php

$db = app()->db;
$currentUser = app()->user;

use Brick\Postcode\PostcodeFormatter;

$PostcodeFormatter = new PostcodeFormatter();

try {
  if (isset($_POST['street-and-number']) && $_POST['street-and-number'] && isset($_POST['town-city']) && $_POST['town-city'] && isset($_POST['post-code']) && $_POST['post-code']) {
    $addr = [
      'streetAndNumber' => mb_convert_case(trim($_POST['street-and-number']), MB_CASE_TITLE, "UTF-8"),
      'city' => mb_convert_case(trim($_POST['town-city']), MB_CASE_TITLE, "UTF-8"),
      'postCode' => (string) $PostcodeFormatter->format('GB', trim($_POST['post-code'])),
    ];
    if (isset($_POST['flat-building']) && $_POST['flat-building']) {
      $addr += ['flatOrBuilding' => mb_convert_case(trim($_POST['flat-building']), MB_CASE_TITLE, "UTF-8")];
    }
    if (isset($_POST['county-province']) && $_POST['county-province']) {
      $addr += ['county' => mb_convert_case(trim($_POST['county-province']), MB_CASE_TITLE, "UTF-8")];
    }
    $addr = json_encode($addr);
    $currentUser->setUserOption('MAIN_ADDRESS', $addr);
    $_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate'] = true;
  }
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate'] = false;
}

header("Location: " . autoUrl("my-account/address"));