<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

use Brick\Postcode\PostcodeFormatter;

$PostcodeFormatter = new PostcodeFormatter();

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Session::stagesOrder();

// Validate and update user info

$good = false;
if (isset($_POST['street-and-number']) && $_POST['street-and-number'] && isset($_POST['town-city']) && $_POST['town-city'] && isset($_POST['post-code']) && $_POST['post-code']) {
  $allowed = true;
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
  $session->getUser()->setUserOption('MAIN_ADDRESS', $addr);
  $good = true;
}

if ($good) {
  // If all good,

  // Set complete
  $session->completeTask('address_details');

  header('location: ' . autoUrl('onboarding/go'));
} else {
  $_SESSION['FormError'] = true;
  header('location: ' . autoUrl('onboarding/go/start-task'));
}
