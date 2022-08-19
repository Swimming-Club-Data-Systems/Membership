<?php

use function GuzzleHttp\json_encode;

$db = app()->db;
$tenant = app()->tenant;

$user = app()->user;
if (!$user->hasPermissions(['Admin'])) halt(404);

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF token');
  }

  $insert = $db->prepare("INSERT INTO `qualifications` (`ID`, `Name`, `Description`, `DefaultExpiry`, `Show`, `Tenant`) VALUES (?, ?, ?, ?, ?, ?);");

  $id = Ramsey\Uuid\Uuid::uuid4()->toString();

  if (!isset($_POST['qualification-name']) || (mb_strlen(trim($_POST['qualification-name'])) == 0)) {
    throw new Exception('You must provide a name for this qualification');
  }

  $schedule = null;

  if ($_POST['expires'] == 'yes') {
    $scale = $_POST['expires-when-type'];
    $value = (int) $_POST['expires-when'];
    $schedule = [
      'type' => $scale,
      'value' => $value,
    ];
  }

  $expiry = [
    'expires' => $_POST['expires'] == 'yes',
    'expiry_schedule' => $schedule,
  ];

  $expiry = json_encode($expiry);

  $insert->execute([
    $id,
    mb_convert_case(trim($_POST['qualification-name']), MB_CASE_TITLE_SIMPLE),
    trim($_POST['qualification-description']),
    $expiry,
    (int) true,
    $tenant->getId(),
  ]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewQualificationSuccess'] = true;

  http_response_code(302);
  header('location: ' . autoUrl('qualifications/' . $id));

} catch (Exception $e) {

  $_SESSION['TENANT-' . app()->tenant->getId()]['NewQualificationError'] = $e->getMessage();

  http_response_code(302);
  header('location: ' . autoUrl('qualifications/new'));

}