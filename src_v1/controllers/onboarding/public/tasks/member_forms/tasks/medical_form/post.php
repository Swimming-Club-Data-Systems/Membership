<?php

$db = app()->db;

$session = \SCDS\Onboarding\Session::retrieve($_SESSION['OnboardingSessionId']);

if ($session->status == 'not_ready') halt(404);

$user = $session->getUser();

$tenant = app()->tenant;

$logos = app()->tenant->getKey('LOGO_DIR');

$stages = $session->stages;

$tasks = \SCDS\Onboarding\Member::stagesOrder();

// Get member
$onboardingMember = \SCDS\Onboarding\Member::retrieveById($id);

$member = $onboardingMember->getMember();

if (!\SCDS\CSRF::verify()) halt(403);

$conditions = $allergies = $medication = $withhold = $gpName = $gpAddress = $gpPhone = null;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberType;
use Respect\Validation\Validator as v;

$gpAddress = json_encode([]);

// Deal with data
if (isset($_POST['medConDisDetails']) && mb_strlen(trim($_POST['medConDisDetails'])) > 0) {
  $conditions = trim($_POST['medConDisDetails']);
}

if (isset($_POST['allergiesDetails']) && mb_strlen(trim($_POST['allergiesDetails'])) > 0) {
  $allergies = trim($_POST['allergiesDetails']);
}

if (isset($_POST['medicineDetails']) && mb_strlen(trim($_POST['medicineDetails'])) > 0) {
  $medication = trim($_POST['medicineDetails']);
}

if ($member->getAge() < 18) {
  if (isset($_POST['gp-name']) && mb_strlen(trim($_POST['gp-name'])) > 0) {
    $gpName = trim($_POST['gp-name']);
  }

  if (isset($_POST['gp-address']) && mb_strlen(trim($_POST['gp-address'])) > 0) {
    $gpAddress = json_encode(explode("\r\n", trim($_POST['gp-address'])));
  }

  if (isset($_POST['gp-phone']) && mb_strlen(trim($_POST['gp-phone'])) > 0 && v::phone()->validate($_POST['gp-phone'])) {
    try {
      $mobile = PhoneNumber::parse($_POST['gp-phone'], 'GB');
      $gpPhone = $mobile->format(PhoneNumberFormat::E164);
    } catch (Exception $e) {
    }
  }
}

$withhold = true;
if (isset($_POST['emergency-medical-auth'])) {
  $withhold = false;
}

// Get count
$getCount = $db->prepare("SELECT COUNT(*) FROM `memberMedical` WHERE `MemberID` = ?");
$getCount->execute([
  $member->getId(),
]);

if ($getCount->fetchColumn() == 0) {
  $insert = $db->prepare("INSERT INTO `memberMedical` (`MemberID`, `Conditions`, `Allergies`, `Medication`, `GPName`, `GPAddress`, `GPPhone`, `WithholdConsent`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $insert->execute([
    $member->getId(),
    $conditions,
    $allergies,
    $medication,
    $gpName,
    $gpAddress,
    $gpPhone,
    (int) $withhold,
  ]);
} else {
  $update = $db->prepare("UPDATE `memberMedical` SET `Conditions` = ?, `Allergies` = ?, `Medication` = ?, `GPName` = ?, `GPAddress` = ?, `GPPhone` = ?, `WithholdConsent` = ? WHERE `MemberID` = ?");
  $update->execute([
    $conditions,
    $allergies,
    $medication,
    $gpName,
    $gpAddress,
    $gpPhone,
    (int) $withhold,
    $member->getId(),
  ]);
}

$onboardingMember->completeTask('medical_form');

http_response_code(302);
if ($onboardingMember->session->checkMemberTasksComplete()) {
  header("Location: " . autoUrl('onboarding/go'));
} else {
  header("Location: " . autoUrl('onboarding/go/start-task'));
}