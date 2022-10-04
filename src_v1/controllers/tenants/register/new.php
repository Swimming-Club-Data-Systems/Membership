<?php

/**
 * New tenant registration
 * 
 * THIS IS AN INITIAL VERSION WITH VERY LITTLE VALIDATION
 * IT IS NOT FOR PRODUCTION
 */

use Respect\Validation\Validator as v;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$mobile = null;
try {
  $number = PhoneNumber::parse($_POST['user-phone'], 'GB');
  $mobile = $number->format(PhoneNumberFormat::E164);
} catch (PhoneNumberParseException $e) {
  // 'The string supplied is too short to be a phone number.'
  $status = false;
}

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

$db = app()->db;

// Create tenant if not exists
$getCount = $db->prepare("SELECT COUNT(*) FROM tenants WHERE Code = ?");

$code = null;
$email = trim(mb_strtolower($_POST['user-email']));

if ($_POST['club'] != 'select' && $_POST['club'] != 'not-se') {
  $getCount->execute([
    mb_strtoupper($_POST['club'])
  ]);
  if ($getCount->fetchColumn() == 0) {
    // All good to go
    $code = mb_strtoupper($_POST['club']);
  } else {
    halt(403);
  }
}

use Ramsey\Uuid\Uuid;

$uuid = Uuid::uuid4();

$add = $db->prepare("INSERT INTO tenants (`Name`, `Code`, `Website`, `Email`, `Verified`, `UniqueID`) VALUES (?, ?, ?, ?, ?, ?)");
$add->execute([
  $_POST['CLUB_NAME'],
  $code,
  $_POST['CLUB_WEBSITE'],
  mb_strtolower($_POST['user-email']),
  0,
  $uuid->toString(),
]);

$id = $db->lastInsertId();

$tenant = $clubObject = Tenant::fromId($id);

$tenant->setKey('CLUB_NAME', $_POST['CLUB_NAME']);
$tenant->setKey('CLUB_SHORT_NAME', $_POST['CLUB_SHORT_NAME']);

$addr = $_POST['CLUB_ADDRESS'];
$addr = str_replace("\r\n", "\n", $addr);
$addr = explode("\n", $addr);
$addr = json_encode($addr);

$tenant->setKey('CLUB_ADDRESS', trim($addr));
$tenant->setKey('SYSTEM_COLOUR', '#0049a0');
$tenant->setKey('CLUB_EMAIL', $email);

if (in_array($_POST['club'], $clubs)) {
  $tenant->setKey('ASA_DISTRICT', $clubs[$_POST['CLUB_INFO']]['District']);
  $tenant->setKey('ASA_COUNTY', $clubs[$_POST['CLUB_INFO']]['County']);
}

// Create user
$insert = $db->prepare("INSERT INTO users (EmailAddress, `Password`, Forename, Surname, Mobile, EmailComms, MobileComms, RR, Tenant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$addAccessLevel = $db->prepare("INSERT INTO `permissions` (`Permission`, `User`) VALUES (?, ?)");

$insert->execute([
  $email,
  password_hash($_POST['user-password'], PASSWORD_BCRYPT),
  trim($_POST['fn']),
  trim($_POST['ln']),
  $mobile,
  0,
  0,
  0,
  $tenant->getId()
]);

$uid = $db->lastInsertId();

$addAccessLevel->execute([
  'Admin',
  $uid
]);

$message = '<p>Hello ' . htmlspecialchars(trim($_POST['fn'])) . '. Thanks for signing up for SCDS Membership MT.</p>';
$message .= '<p>Your club\'s system url is ' . htmlspecialchars(autoUrl($tenant->getCodeId())) . '.</p>';
$message .= '<p>Contact support@myswimmingclub.uk for support.</p>';

// Not quite ready yet
// notifySend(null, 'Welcome to Membership MT by SCDS', $message, trim($_POST['fn']) . ' ' . trim($_POST['sn']), $email);

header("location: " . autoUrl($tenant->getCodeId()));

/**
 * 
 * Options which must be set
 * 
 * CLUB_NAME
 * CLUB_SHORT_NAME
 * CLUB_ADDRESS
 * SYSTEM_COLOUR : #0049a0
 * CLUB_EMAIL
 * 
 * For now also ensure the following are set
 * 
 * ClubFeesType : NSwimmers
 * FeesWithMultipleSquads : Full
 * 
 */
