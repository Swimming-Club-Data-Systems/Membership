<?php

use function GuzzleHttp\json_decode;

$db = app()->db;
$tenant = app()->tenant;

$user = app()->user;
if (!$user->hasPermissions(['Admin'])) halt(404);

$getQualifications = $db->prepare("SELECT `Name`, `Description`, `DefaultExpiry` FROM `qualifications` WHERE `Show` AND `ID` = ? AND `Tenant` = ?");
$getQualifications->execute([
  $id,
  $tenant->getId(),
]);
$qualification = $getQualifications->fetch(PDO::FETCH_ASSOC);

if (!$qualification) {
  halt(404);
}

$expiry = json_decode($qualification['DefaultExpiry']);

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!\SCDS\CSRF::verify()) {
    throw new Exception('Invalid CSRF token');
  }

  $update = $db->prepare("UPDATE `qualifications` SET `Name` = ?, `Description` = ?, `DefaultExpiry` = ? WHERE `ID` = ?;");

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

  $update->execute([
    mb_convert_case(trim($_POST['qualification-name']), MB_CASE_TITLE_SIMPLE),
    trim($_POST['qualification-description']),
    $expiry,
    $id,
  ]);

  $_SESSION['TENANT-' . app()->tenant->getId()]['EditQualificationSuccess'] = true;

  http_response_code(302);
  header('location: ' . autoUrl('qualifications/' . $id));

} catch (Exception $e) {

  $_SESSION['TENANT-' . app()->tenant->getId()]['EditQualificationError'] = true;

  http_response_code(302);
  header('location: ' . autoUrl("qualifications/$id/edit"));

}