<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

$getYear = $db->prepare("SELECT `Name`, `StartDate`, `EndDate` FROM `membershipYear` WHERE `ID` = ? AND `Tenant` = ?");
$getYear->execute([
  $id,
  $tenant->getId(),
]);
$year = $getYear->fetch(PDO::FETCH_ASSOC);

if (!$year) halt(404);

try {

  if (!isset($_POST['name']) || !isset($_POST['start']) || !isset($_POST['end'])) {
    throw new Exception('Missing data');
  }

  if (mb_strlen(trim($_POST['name'])) == 0) {
    throw new Exception('Missing name');
  }

  $startDate = new DateTime($_POST['start'], new DateTimeZone('Europe/London'));
  $endDate = new DateTime($_POST['end'], new DateTimeZone('Europe/London'));

  $insert = $db->prepare("UPDATE `membershipYear` SET `Name` = ?, `StartDate` = ?, `EndDate` = ? WHERE `ID` = ?");
  $insert->execute([
    trim($_POST['name']),
    $startDate->format('Y-m-d'),
    $endDate->format('Y-m-d'),
    $id,
  ]);

  http_response_code(302);
  header("location: " . autoUrl("memberships/years/$id"));

} catch (Exception $e) {
  // Broke
  http_response_code(302);
  header("location: " . autoUrl("memberships/years/$id/edit"));
}