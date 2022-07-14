<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$member = null;
try {
  $member = new Member($id);
} catch (Exception $e) {
  halt(404);
}

$user = $member->getUser();

$currentUser = app()->user;
if (!$currentUser->hasPermissions(['Admin'])) halt(404);

try {

  if (!\SCDS\CSRF::verify()) throw new Exception('Invalid CSRF token');

  // Check the qualification exists at this tenant
  $date = new DateTime('now', new DateTimeZone('Europe/London'));
  $getQualifications = $db->prepare("SELECT `Name`, `ID` FROM `qualifications` WHERE `ID` = :qual AND `Show` AND `Tenant` = :tenant AND (`ID` NOT IN (SELECT `Qualification` AS `ID` FROM `qualificationsMembers` WHERE `Member` = :member AND ValidFrom <= :date AND (ValidUntil >= :date OR ValidUntil IS NULL))) ORDER BY `Name` ASC;");
  $getQualifications->execute([
    'qual' => $_POST['qualification'],
    'tenant' => $tenant->getId(),
    'member' => $member->getId(),
    'date' => $date->format('Y-m-d'),
  ]);
  $qualification = $getQualifications->fetch(PDO::FETCH_ASSOC);

  if (!$qualification) {
    throw new Exception('No such qualification');
  }

  $validFrom = (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d');
  $validTo = null;

  try {
    $validFrom = (new DateTime($_POST['valid-from'], new DateTimeZone('Europe/London')))->format('Y-m-d');
    if (isset($_POST['expires']) && $_POST['expires'] == 'yes') {
      $validTo = (new DateTime($_POST['valid-to'], new DateTimeZone('Europe/London')))->format('Y-m-d');
    }
  } catch (Exception $e) {
    // Ignore
  }

  $notes = null;
  if (isset($_POST['notes']) && mb_strlen(trim($_POST['notes'])) > 0) {
    $notes = trim($_POST['notes']);
  }

  $insert = $db->prepare("INSERT INTO `qualificationsMembers` (`ID`, `Qualification`, `Member`, `ValidFrom`, `ValidUntil`, `Notes`) VALUES (?, ?, ?, ?, ?, ?)");
  $insert->execute([
    Ramsey\Uuid\Uuid::uuid4()->toString(),
    $_POST['qualification'],
    $member->getId(),
    $validFrom,
    $validTo,
    $notes,
  ]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewQualificationSuccess'] = true;

  http_response_code(302);
  header('location: ' . autoUrl("members/$id#qualifications"));
} catch (Exception $e) {
  // Error

  $message = $e->getMessage();
  if (get_class($e) == 'PDOException') {
    $message = 'A database error occurred';
  }

  $_SESSION['TENANT-' . app()->tenant->getId()]['FormError'] = $message;

  http_response_code(302);
  header('location: ' . autoUrl("members/$id/qualifications/new"));
}
