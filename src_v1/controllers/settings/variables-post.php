<?php

$db = app()->db;

$clubs = [];
$row = 1;
if (($handle = fopen(BASE_PATH . "includes/regions/clubs.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000)) !== false) {
    if ($row > 1) {
      $clubs += [$data[1] => [
        'Name' => $data[0],
        'Code' => $data[1],
        'District' => $data[2],
        'County' => $data[3],
        'MeetName' => $data[4],
      ]];
    }
    $row++;
  }
  fclose($handle);
}

$vars = [
  'CLUB_NAME' => null,
  'CLUB_SHORT_NAME' => null,
  'ASA_CLUB_CODE' => null,
  'CLUB_EMAIL' => null,
  'CLUB_TRIAL_EMAIL' => null,
  'EMAIL_DOMAIN' => null,
  'CLUB_WEBSITE' => null,
  'SENDGRID_API_KEY' => null,
  'CLUB_ADDRESS' => null,
  'SYSTEM_COLOUR' => null,
  'EMERGENCY_MESSAGE' => false,
  'GOCARDLESS_ACCESS_TOKEN' => null,
  'GOCARDLESS_WEBHOOK_KEY' => null,
  'EMERGENCY_MESSAGE_TYPE' => 'NONE',
  'SHOW_LOGO' => false,
  'LOGO_DIR' => null,
  'GLOBAL_PERSONAL_KEY' => null,
  'GLOBAL_PERSONAL_KEY_ID_NUMBER' => null,
  'REQUIRE_SQUAD_REP_FOR_APPROVAL' => true,
  'HIDE_MOVE_FEE_INFO' => false,
  'DISPLAY_NAME_FORMAT' => 'FL',
  'DEFAULT_GALA_PROCESSING_FEE' => 0,
];

try {
  foreach ($vars as $key => $value) {
    if (isset($_POST[$key]) && $_POST[$key] != null && !getenv($key)) {
      app()->tenant->setKey($key, $_POST[$key]);
    }
  }

  if (isset($_POST['CLUB_INFO']) && app()->tenant->getKey('ASA_CLUB_CODE') != $_POST['CLUB_INFO'] && $_POST['CLUB_INFO'] != '0000') {
    app()->tenant->setKey('CLUB_NAME', $clubs[$_POST['CLUB_INFO']]['Name']);
    app()->tenant->setKey('CLUB_SHORT_NAME', $clubs[$_POST['CLUB_INFO']]['MeetName']);
    app()->tenant->setKey('ASA_CLUB_CODE', $clubs[$_POST['CLUB_INFO']]['Code']);
    app()->tenant->setKey('ASA_DISTRICT', $clubs[$_POST['CLUB_INFO']]['District']);
    app()->tenant->setKey('ASA_COUNTY', $clubs[$_POST['CLUB_INFO']]['County']);
  }

  $message = $_POST['EMERGENCY_MESSAGE'];
  if (mb_strlen($message) == 0) {
    $message = null;
  }
  app()->tenant->setKey('EMERGENCY_MESSAGE', $message);

  $type = $_POST['EMERGENCY_MESSAGE_TYPE'];
  if ($type == 'NONE' || $type == 'SUCCESS' || $type == 'WARN' || $type == 'DANGER') {
    if ($message == null) {
      $type = 'NONE';
    }
    app()->tenant->setKey('EMERGENCY_MESSAGE_TYPE', $type);
  }

  // DISPLAY_NAME_FORMAT
  $type = $_POST['DISPLAY_NAME_FORMAT'];
  if ($type == 'FL' || $type == 'L,F') {
    app()->tenant->setKey('DISPLAY_NAME_FORMAT', $type);
  }

  $hide = 1;
  if (isset($_POST['HIDE_MEMBER_ATTENDANCE']) && bool($_POST['HIDE_MEMBER_ATTENDANCE'])) {
    $hide = 0;
  }
  app()->tenant->setKey('HIDE_MEMBER_ATTENDANCE', $hide);

  if (app()->tenant->getKey('LOGO_DIR')) {
    app()->tenant->setKey('SHOW_LOGO', (int) bool($_POST['SHOW_LOGO']));
  }

  // 'HIDE_CONTACT_TRACING_FROM_PARENTS' => false,
  $hide = 0;
  if (!isset($_POST['HIDE_CONTACT_TRACING_FROM_PARENTS']) || !bool($_POST['HIDE_MEMBER_ATTENDANCE'])) {
    $hide = 1;
  }
  app()->tenant->setKey('HIDE_CONTACT_TRACING_FROM_PARENTS', $hide);

  // 'BLOCK_SQUAD_REPS_FROM_NOTIFY' => false,
  $hide = 0;
  if (!isset($_POST['BLOCK_SQUAD_REPS_FROM_NOTIFY']) || !bool($_POST['BLOCK_SQUAD_REPS_FROM_NOTIFY'])) {
    $hide = 1;
  }
  app()->tenant->setKey('BLOCK_SQUAD_REPS_FROM_NOTIFY', $hide);

  // 'REQUIRE_FULL_REGISTRATION' => true,
  $hide = 1;
  if (!isset($_POST['REQUIRE_FULL_REGISTRATION']) || !bool($_POST['REQUIRE_FULL_REGISTRATION'])) {
    $hide = 0;
  }
  app()->tenant->setKey('REQUIRE_FULL_REGISTRATION', $hide);

  // 'REQUIRE_FULL_RENEWAL' => true,
  $hide = 1;
  if (!isset($_POST['REQUIRE_FULL_RENEWAL']) || !bool($_POST['REQUIRE_FULL_RENEWAL'])) {
    $hide = 0;
  }
  app()->tenant->setKey('REQUIRE_FULL_RENEWAL', $hide);

  // 'REQUIRE_SQUAD_REP_FOR_APPROVAL' => true,
  $hide = 1;
  if (!isset($_POST['REQUIRE_SQUAD_REP_FOR_APPROVAL']) || !bool($_POST['REQUIRE_SQUAD_REP_FOR_APPROVAL'])) {
    $hide = 0;
  }
  app()->tenant->setKey('REQUIRE_SQUAD_REP_FOR_APPROVAL', $hide);

  // 'ENABLE_BILLING_SYSTEM' => true,
  $hide = 1;
  if (!isset($_POST['ENABLE_BILLING_SYSTEM']) || !bool($_POST['ENABLE_BILLING_SYSTEM'])) {
    $hide = 0;
  }
  app()->tenant->setKey('ENABLE_BILLING_SYSTEM', $hide);
  
  // 'HIDE_MOVE_FEE_INFO' => false,
  $hide = 1;
  if (!isset($_POST['HIDE_MOVE_FEE_INFO']) || !bool($_POST['HIDE_MOVE_FEE_INFO'])) {
    $hide = 0;
  }
  app()->tenant->setKey('HIDE_MOVE_FEE_INFO', $hide);

  $fee = 0;
  if (isset($_POST['DEFAULT_GALA_PROCESSING_FEE'])) {
    $fee = MoneyHelpers::decimalToInt($_POST['DEFAULT_GALA_PROCESSING_FEE']);
  }
  app()->tenant->setKey('DEFAULT_GALA_PROCESSING_FEE', $fee);

  $vars['CLUB_ADDRESS'] = null;
  $addr = $_POST['CLUB_ADDRESS'];
  $addr = str_replace("\r\n", "\n", $addr);
  $addr = explode("\n", $addr);
  for ($i = 0; $i < sizeof($addr); $i++) {
    $addr[0] = trim($addr[0], " \t\n\r\0\x0B,");
  }
  $addr = json_encode($addr);
  app()->tenant->setKey('CLUB_ADDRESS', $addr);

  $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-SAVED'] = true;
} catch (Exception $e) {
  reportError($e);
  $_SESSION['TENANT-' . app()->tenant->getId()]['PCC-ERROR'] = true;
}

header("Location: " . autoUrl("settings/variables"));
