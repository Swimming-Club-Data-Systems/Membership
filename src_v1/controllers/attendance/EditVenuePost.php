<?php

$db = app()->db;
$tenant = app()->tenant;

$data = $db->prepare("SELECT `VenueName`, `Location` FROM sessionsVenues WHERE VenueID = ? AND Tenant = ?");
$data->execute([
  $id,
  $tenant->getId()
]);
$venue = $data->fetch(PDO::FETCH_ASSOC);

if (!$venue) {
  halt(404);
}

$edit = $db->prepare("UPDATE sessionsVenues SET VenueName = ?, Location = ? WHERE VenueID = ? AND Tenant = ?");

if ($_POST['name'] != "" && $_POST['name'] != null && $_POST['address'] != "" && $_POST['address'] != null) {
  try {
    $db->beginTransaction();
    $edit->execute([
      $_POST['name'],
      $_POST['address'],
      $id,
      $tenant->getId()
    ]);
    $db->commit();
    $_SESSION['TENANT-' . app()->tenant->getId()]['EditVenueSuccess'] = true;
  } catch (Exception $e) {
    $db->rollback();
    halt(500);
  }
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['EditVenueError'] = [
    "Status"      => true,
    "Data"        => $_POST
  ];
}

header("Location: " . autoUrl("attendance/venues/" . $id));
