<?php

if (!SCDS\CSRF::verify()) {
  halt(403);
}

$db = app()->db;
$tenant = app()->tenant;

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  $getMed = $db->prepare("SELECT MForename, MSurname, Conditions, Allergies,
  Medication FROM `members` LEFT JOIN `memberMedical` ON members.MemberID =
  memberMedical.MemberID WHERE members.Tenant = ? AND members.MemberID = ? AND members.UserID = ?");
  $getMed->execute([
    $tenant->getId(),
    $id,
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
  ]);
} else {
  $getMed = $db->prepare("SELECT MForename, MSurname, Conditions, Allergies,
  Medication FROM `members` LEFT JOIN `memberMedical` ON members.MemberID =
  memberMedical.MemberID WHERE members.Tenant = ? AND members.MemberID = ?");
  $getMed->execute([
    $tenant->getId(),
    $id
  ]);
}

$row = $getMed->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$member = new Member($id);

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberType;
use Respect\Validation\Validator as v;

setupMedicalInfo($id);

try {
  $conditions = $allergies = $medication = $withhold = $gpName = $gpAddress = $gpPhone = null;

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
  header("Location: " . autoUrl("members/" . $id . "/medical"));
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = "
	<div class=\"alert alert-danger\">
	<strong>An error occured when we tried to update our records</strong>
	<p class=\"mb-0\">Please try again.</p></div>";
  header("Location: " . autoUrl("members/" . $id . "/medical"));
}
