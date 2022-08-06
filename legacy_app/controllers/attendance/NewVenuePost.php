<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$add = $db->prepare("INSERT INTO sessionsVenues (`VenueName`, `Location`, `Tenant`) VALUES (?, ?, ?)");

if ($_POST['name'] != "" && $_POST['name'] != null && $_POST['address'] != "" && $_POST['address'] != null) {
  try {
    $db->beginTransaction();
    $add->execute([
      $_POST['name'],
      $_POST['address'],
      $tenant->getID()
    ]);
    $id = $db->lastInsertId();
    $db->commit();
    $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['NewVenueSuccess'] = true;
    header("Location: " . autoUrl("attendance/venues/" . $id));
  } catch (Exception $e) {
    $db->rollback();
    halt(500);
  }
} else {
  $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['NewVenueError'] = [
    "Status"      => true,
    "Data"        => $_POST
  ];
  header("Location: " . autoUrl("attendance/venues/new"));
}
