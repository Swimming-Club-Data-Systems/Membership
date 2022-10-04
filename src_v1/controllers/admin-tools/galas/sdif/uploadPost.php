<?php

$db = app()->db;
$tenant = app()->tenant;

$checkForExistingGala = $db->prepare("SELECT `Meet` FROM `meetsWithResults` WHERE Tenant = ? AND `Name` = ? AND `City` = ? AND (`Start` = ? OR `End` = ?) AND `Course` = ?");
$checkForExistingResult = $db->prepare("SELECT COUNT(*) FROM `meetResults` WHERE `Member` = ? AND `Date` = ? AND `IntTime` = ? AND `ChronologicalOrder` = ? AND `Round` = ? AND `Stroke` = ? AND `Distance` = ? AND `Course` = ?");
$addResult = $db->prepare("INSERT INTO `meetResults` (`Meet`, `Member`, `Date`, `Time`, `IntTime`, `ChronologicalOrder`, `Round`, `Stroke`, `Distance`, `Course`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$getMember = $db->prepare("SELECT MemberID FROM members WHERE ASANumber = ?");
$getGala = $db->prepare("SELECT COUNT(*) FROM galas WHERE GalaID = ? AND Tenant = ?");
$gala = null;
if (isset($_POST['gala']) && $_POST['gala'] != 0) {
  $getGala->execute([
    $_POST['gala'],
    $tenant->getId()
  ]);
  if ($getGala->fetchColumn() == 1) {
    $gala = $_POST['gala'];
  }
}

$formInvalid = false;
if (!\SCDS\FormIdempotency::verify()) {
  $formInvalid = true;
}
if (!\SCDS\CSRF::verify()) {
  $formInvalid = true;
}

if (!$formInvalid) {
  try {
    $db->beginTransaction();
    for ($i = 0; $i < sizeof($_FILES['file-upload']['tmp_name']); $i++) {
      if (is_uploaded_file($_FILES['file-upload']['tmp_name'][$i])) {

        if (bool($_FILES['file-upload']['error'][$i])) {
          // Error
          // reportError($_FILES['file-upload']['error'][$i]);
          if ($_FILES['file-upload']['error'][$i] == 2) {
            // Too large
            $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError'] = true;
          } else {
            $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
          }
          throw new Exception();
        } else if ($_FILES['file-upload']['type'][$i] != 'text/plain' && $_FILES['file-upload']['type'][$i] != 'application/octet-stream') {
          // Probably not a text file
          reportError($_FILES['file-upload']['type'][$i]);
          $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
          throw new Exception();
        } else if ($_FILES['file-upload']['size'][$i] > 3000000) {
          // Too large, stop
          // reportError($_FILES['file-upload']['size'][$i]);
          $_SESSION['TENANT-' . app()->tenant->getId()]['TooLargeError'] = true;
          throw new Exception();
        } else {
          $filePointer = fopen($_FILES['file-upload']['tmp_name'][$i], 'r');

          $meetData = null;

          while (!feof($filePointer)) {
            $line = fgets($filePointer);
            // pre($line);
          
            if (mb_strlen($line) > 1 && substr($line, 0, 2) == 'B1') {
              $b1 = new \CLSASC\SDIF\B1();
              $b1->createFromLine($line);
              $meetData = [
                'name' => $b1->getMeetName(),
                'city' => $b1->getMeetCity(),
                'start' => $b1->getMeetStartDate(),
                'end' => $b1->getMeetEndDate(),
                'course' => $b1->getCourse()
              ];

              $startDate = null;
              if ($meetData['start']) {
                $startDate = $meetData['start']->format("Y-m-d");
              }
              $endDate = null;
              if ($meetData['end']) {
                $endDate = $meetData['end']->format("Y-m-d");
              }

              $checkForExistingGala->bindParam(1, $tenant->getId(), PDO::PARAM_INT);
              $checkForExistingGala->bindParam(2, $meetData['name'], PDO::PARAM_STR);
              $checkForExistingGala->bindParam(3, $meetData['city'], PDO::PARAM_STR);
              if ($startDate != null) {
                $checkForExistingGala->bindParam(4, $startDate, PDO::PARAM_STR);
              } else {
                $checkForExistingGala->bindParam(4, $endDate, PDO::PARAM_NULL);
              }
              if ($endDate != null) {
                $checkForExistingGala->bindParam(5, $endDate, PDO::PARAM_STR);
              } else {
                $checkForExistingGala->bindParam(5, $endDate, PDO::PARAM_NULL);
              }
              $checkForExistingGala->bindParam(6, $meetData['course'], PDO::PARAM_STR);
              $checkForExistingGala->execute();
              $meet = $checkForExistingGala->fetchColumn();

              if ($meet == null) {
                // New gala add record
                $addGala = $db->prepare("INSERT INTO meetsWithResults (`Name`, `City`, `Start`, `End`, `Course`, `Gala`, Tenant) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $addGala->execute([
                  $meetData['name'],
                  $meetData['city'],
                  $startDate,
                  $endDate,
                  $meetData['course'],
                  $gala,
                  $tenant->getId(),
                ]);
                $meet = $db->lastInsertId();
              }
            }
          
            if (mb_strlen($line) > 1 && substr($line, 0, 2) == 'D0') {
              $d0 = new \CLSASC\SDIF\D0();
              $d0->createFromLine($line);

              $getMember->execute([
                $d0->getSwimmerNumber()
              ]);
              $member = $getMember->fetchColumn();
              if ($member != null) {
                $prelims = null;
                if ($d0->hasPrelimTime()) {
                  $time = $d0->getPrelim();
                  $checkForExistingResult->execute([
                    $member,
                    $d0->getDateOfSwimDB(),
                    \CLSASC\SDIF\D0::timeAsInt($time),
                    0,
                    'P',
                    $d0->getStroke(),
                    $d0->getDistance(),
                    $d0->getPrelimCourse(),
                  ]);
                  $count = $checkForExistingResult->fetchColumn();
                  if ($count == 0) {
                    // Add new result
                    // (`Meet`, `Date`, `Time`, `IntTime`, `ChronologicalOrder`, `Round`, `Stroke`, `Distance`, `Course`)
                    $addResult->execute([
                      $meet,
                      $member,
                      $d0->getDateOfSwimDB(),
                      $time,
                      \CLSASC\SDIF\D0::timeAsInt($time),
                      0,
                      'P',
                      $d0->getStroke(),
                      $d0->getDistance(),
                      $d0->getPrelimCourse(),
                    ]);
                  }
                }

                if ($d0->hasSwimOffTime()) {
                  $time = $d0->getSwimOff();
                  $checkForExistingResult->execute([
                    $member,
                    $d0->getDateOfSwimDB(),
                    \CLSASC\SDIF\D0::timeAsInt($time),
                    1,
                    'S',
                    $d0->getStroke(),
                    $d0->getDistance(),
                    $d0->getSwimOffCourse(),
                  ]);
                  $count = $checkForExistingResult->fetchColumn();
                  if ($count == 0) {
                    // Add new result
                    // (`Meet`, `Date`, `Time`, `IntTime`, `ChronologicalOrder`, `Round`, `Stroke`, `Distance`, `Course`)
                    $addResult->execute([
                      $meet,
                      $member,
                      $d0->getDateOfSwimDB(),
                      $time,
                      \CLSASC\SDIF\D0::timeAsInt($time),
                      1,
                      'S',
                      $d0->getStroke(),
                      $d0->getDistance(),
                      $d0->getSwimOffCourse(),
                    ]);
                  }
                }

                if ($d0->hasFinalsTime()) {
                  $time = $d0->getFinals();
                  $checkForExistingResult->execute([
                    $member,
                    $d0->getDateOfSwimDB(),
                    \CLSASC\SDIF\D0::timeAsInt($time),
                    2,
                    'F',
                    $d0->getStroke(),
                    $d0->getDistance(),
                    $d0->getFinalsCourse(),
                  ]);
                  $count = $checkForExistingResult->fetchColumn();
                  if ($count == 0) {
                    // Add new result
                    // (`Meet`, `Date`, `Time`, `IntTime`, `ChronologicalOrder`, `Round`, `Stroke`, `Distance`, `Course`)
                    $addResult->execute([
                      $meet,
                      $member,
                      $d0->getDateOfSwimDB(),
                      $time,
                      \CLSASC\SDIF\D0::timeAsInt($time),
                      2,
                      'F',
                      $d0->getStroke(),
                      $d0->getDistance(),
                      $d0->getFinalsCourse(),
                    ]);
                  }
                }
              }
            }
          }
          
          fclose($filePointer);
        }
      } else {
        $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
        throw new Exception();
      }
    }
    $db->commit();
    $_SESSION['TENANT-' . app()->tenant->getId()]['UploadSuccess'] = true;
  } catch (Exception | Error $e) {
    $db->rollBack();
    $_SESSION['TENANT-' . app()->tenant->getId()]['UploadError'] = true;
    reportError($e);
  }
} else if ($formInvalid) {
  $_SESSION['TENANT-' . app()->tenant->getId()]['FormError'] = true;
}

header("Location: " . autoUrl("admin/galas/sdif/upload"));