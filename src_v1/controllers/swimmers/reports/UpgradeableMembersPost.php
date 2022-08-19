<?php

$db = app()->db;
$tenant = app()->tenant;

$checkCategory = $db->prepare("SELECT COUNT(*) FROM `clubMembershipClasses` WHERE `Type` = ? AND `ID` = ? AND `Tenant` = ?");

try {

  $db->beginTransaction();

  $changeCat = $db->prepare("UPDATE members SET NGBCategory = ? WHERE MemberID = ?");

  $date = new DateTime('-9 years last day of December', new DateTimeZone('Europe/London'));
  $now = new DateTime('now', new DateTimeZone('Europe/London'));

  $findNGBCategory = $db->prepare("SELECT `ID` FROM `clubMembershipClasses` WHERE `Type` = ? AND `Name` LIKE ? AND `Tenant` = ?");
  $findNGBCategory->execute([
    'national_governing_body',
    '%1%',
    $tenant->getId(),
  ]);
  $category = $findNGBCategory->fetchColumn();

  $getMembers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, DateOfBirth dob, NGBCategory cat FROM members WHERE members.Active AND members.Tenant = ? AND DateOfBirth <= ? AND NGBCategory = ? ORDER BY MForename ASC, MSurname ASC");
  $getMembers->execute([
    $tenant->getId(),
    $date->format("Y-m-d"),
    $category
  ]);

  $changed = false;

  while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)) {
    if (isset($_POST["se-cat-" . $member['id']])) {

      $checkCategory->execute([
        'national_governing_body',
        $_POST["se-cat-" . $member['id']],
        $tenant->getId(),
      ]);

      $exists = $checkCategory->fetchColumn();

      if ($_POST["se-cat-" . $member['id']] != $member['cat'] && $exists) {
        // Update the membership category
        $changeCat->execute([
          $_POST["se-cat-" . $member['id']],
          $member['id']
        ]);
        $changed = true;
      }
    }
  }

  $db->commit();
  if ($changed) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSavedSuccessfully'] = true;
  }
} catch (Exception $e) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['CatChangesSaveError'] = true;
  $db->rollBack();
}

header("location: " . autoUrl("members/reports/upgradeable"));
