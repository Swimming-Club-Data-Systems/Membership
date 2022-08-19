<?php

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF token');
  }

  if (!isset($_POST['membership-type']) || !in_array($_POST['membership-type'], ['club', 'national_governing_body', 'other'])) {
    throw new Exception('Unknown membership class type');
  }

  $insert = $db->prepare("INSERT INTO `clubMembershipClasses` (`ID`, `Type`, `Name`, `Description`, `Fees`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?)");

  $description = null;
  if (isset($_POST['class-description']) && mb_strlen(trim($_POST['class-description'])) > 0) {
    $description = trim($_POST['class-description']);
  }

  $newObject = [
    'type' => 'NSwimmers',
    'upgrade_type' => 'TopUp',
    'fees' => [],
  ];

  if ($_POST['membership-type'] == 'national_governing_body') {
    $newObject['type'] = 'PerPerson';
  }
  
  $json = json_encode($newObject);

  $id = Ramsey\Uuid\Uuid::uuid4()->toString();
  $insert->execute([
    $id,
    $_POST['membership-type'],
    mb_convert_case(trim($_POST['class-name']), MB_CASE_TITLE),
    $description,
    $json,
    $tenant->getId(),
  ]);

  http_response_code(302);
  header("location: " . autoUrl("settings/fees/membership-fees/$id"));
} catch (Exception $e) {

  $_SESSION['TENANT-' . app()->tenant->getId()]['Update-Error'] = true;
  http_response_code(302);
  header("location: " . autoUrl("settings/fees/membership-fees/new?type=" . $_POST['membership-type']));
}
