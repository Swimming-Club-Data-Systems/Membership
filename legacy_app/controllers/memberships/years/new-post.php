<?php

if (!app()->user->hasPermission('Admin')) halt(404);

$db = app()->db;
$tenant = app()->tenant;

try {

  if (!isset($_POST['name']) || !isset($_POST['start']) || !isset($_POST['end'])) {
    throw new Exception('Missing data');
  }

  if (mb_strlen(trim($_POST['name'])) == 0) {
    throw new Exception('Missing name');
  }

  $startDate = new DateTime($_POST['start'], new DateTimeZone('Europe/London'));
  $endDate = new DateTime($_POST['end'], new DateTimeZone('Europe/London'));

  $id = \Ramsey\Uuid\Uuid::uuid4();

  $insert = $db->prepare("INSERT INTO `membershipYear` (`ID`, `Name`, `StartDate`, `EndDate`, `Tenant`) VALUES (?, ?, ?, ?, ?)");
  $insert->execute([
    $id,
    trim($_POST['name']),
    $startDate->format('Y-m-d'),
    $endDate->format('Y-m-d'),
    $tenant->getId(),
  ]);

  http_response_code(302);
  header("location: " . autoUrl("memberships/years/$id"));

} catch (Exception $e) {
  // Broke
  http_response_code(302);
  header("location: " . autoUrl("memberships/years/new"));
}