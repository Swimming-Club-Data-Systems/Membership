<?php

if (!\SCDS\FormIdempotency::verify()) {
  halt(404);
}
if (!\SCDS\CSRF::verify()) {
  halt(404);
}

$db = app()->db;
$tenant = app()->tenant->getId();

$findSquadId = $db->prepare("SELECT SquadID FROM squads WHERE SquadName = ? AND Tenant = ?");
$findNGBCategory = $db->prepare("SELECT `ID` FROM `clubMembershipClasses` WHERE `Name` LIKE ? AND `Tenant` = ?");
$insertIntoSwimmers = $db->prepare("INSERT INTO members (MForename, Msurname, DateOfBirth, Gender, ASANumber, NGBCategory, RR, AccessKey, OtherNotes, Tenant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$insertIntoSquads = $db->prepare("INSERT INTO `squadMembers` (`Member`, `Squad`, `Paying`) VALUES (?, ?, ?)");
$setTempASA = $db->prepare("UPDATE members SET ASANumber = ? WHERE MemberID = ?");

if (is_uploaded_file($_FILES['file-upload']['tmp_name'])) {

  if (bool($_FILES['file-upload']['error'])) {
    // Error
    $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
  } else if ($_FILES['file-upload']['type'] != 'text/csv' && $_FILES['file-upload']['type'] != 'application/vnd.ms-excel') {
    // Probably not a CSV
    $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
  } else if ($_FILES['file-upload']['size'] > 30000) {
    // Too large, stop
    $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError'] = true;
  } else {
    $failedSwimmers = [];
    try {
      $db->beginTransaction();

      $filePointer = fopen($_FILES['file-upload']['tmp_name'], 'r');
      while ($row = fgetcsv($filePointer)) {
        $findSquadId->execute([$row[2], $tenant]);

        $fn = mb_convert_case($row[1], MB_CASE_TITLE_SIMPLE);
        $sn = mb_convert_case($row[0], MB_CASE_TITLE_SIMPLE);
        $dob = DateTime::createFromFormat('d/m/Y', $row[3]);
        if ($dob == false) {
          throw new Exception('Incorrectly formatted date of birth');
        }
        $sex = 'Male';
        if ($row[4] == 'Female' || $row[4] == 'F') {
          $sex = 'Female';
        }
        
        $cat = null;
        $findNGBCategory->execute([
          '%' . $row[5] .  '%',
          $tenant->getId(),
        ]);
        $cat = $findNGBCategory->fetchColumn();

        $asa = (int) $row[6];

        if ($squadId = $findSquadId->fetchColumn()) {
          $insertIntoSwimmers->execute([
            $fn,
            $sn,
            $dob->format("Y-m-d"),
            $sex,
            $asa,
            $cat,
            true,
            generateRandomString(6),
            '',
            $tenant
          ]);

          $id = $db->lastInsertId();

          // Add to squads
          $insertIntoSquads->execute([
            $id,
            $squadId,
            (int) true,
          ]);

          if ($asa == 0) {
            $setTempASA->execute([
              app()->tenant->getKey('ASA_CLUB_CODE') . $id,
              $id
            ]);
          }
        } else {
          // Couldn't add swimmer
          $failedSwimmers[] = $fn . ' ' . $sn . ', ' . $asa;
        }
      }

      $db->commit();
      $_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess'] = true;
    } catch (Exception $e) {
      $db->rollBack();
      $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
    }

    if (sizeof($failedSwimmers) > 0) {
      $_SESSION['TENANT-' . app()->tenant->getId()]['FailedSwimmers'] = $failedSwimmers;
    }

  }
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
}

header("Location: " . autoUrl("admin/member-upload"));