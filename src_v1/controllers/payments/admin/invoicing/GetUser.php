<?php

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$db = app()->db;
$tenant = app()->tenant;

if (!isset($_POST['user'])) {
  // return;
}

$getUser = $db->prepare("SELECT UserID, Forename, Surname, EmailAddress, Mobile FROM users INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE users.Tenant = ? AND UserID LIKE ? AND `permissions`.`Permission` = 'Parent'");
$getUser->execute([
  $tenant->getId(),
  $_POST['user']
]);

$row = $getUser->fetch(PDO::FETCH_ASSOC);

$details = [];
if ($row != null) {
  $userObj = new \User($row['UserID']);
  $json = $userObj->getUserOption('MAIN_ADDRESS');
  $address = null;
  if ($json != null) {
    $address = json_decode($json, true);
  }

  $number = null;
  try {
    $number = PhoneNumber::parse((string) $row['Mobile']);
  } catch (Exception $e) {
    $number = false;
  }

  $numberRFC3966 = null;
  $numberNational = null;
  if ($number != false) {
    $numberRFC3966 = $number->format(PhoneNumberFormat::RFC3966);
    $numberNational = $number->format(PhoneNumberFormat::NATIONAL);
  }

  $details = [
    'has_result' => true,
    'id' => $row['UserID'],
    'forename' => $row['Forename'],
    'surname' => $row['Surname'],
    'email' => $row['EmailAddress'],
    'mobile_plain' => $row['Mobile'],
    'mobile_rfc3966' => $numberRFC3966,
    'mobile_national' => $numberNational,
    'address' => $address
  ];
} else {
  $details = [
    'has_result' => false
  ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($details);