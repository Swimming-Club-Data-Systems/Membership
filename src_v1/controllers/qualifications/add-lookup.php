<?php

$db = app()->db;
$tenant = app()->tenant;

$user = app()->user;
if (!$user->hasPermissions(['Admin'])) halt(404);

$json = null;

try {

  $getQualification = $db->prepare("SELECT DefaultExpiry FROM qualifications WHERE ID = ? AND Tenant = ?");
  $getQualification->execute([
    $_POST['qualification'],
    $tenant->getId(),
  ]);
  $qualification = $getQualification->fetch(PDO::FETCH_ASSOC);

  if (!$qualification) halt(404);

  $expiry = json_decode($qualification['DefaultExpiry']);
  $expires = $expiry->expires;

  $plus = null;
  $date = new DateTime('now', new DateTimeZone('Europe/London'));
  if ($expires && $expiry->expiry_schedule->type == 'years') {
    $date->add(new DateInterval('P' . $expiry->expiry_schedule->value . 'Y'));
  } else if ($expires && $expiry->expiry_schedule->type == 'months') {
    $date->add(new DateInterval('P' . $expiry->expiry_schedule->value . 'M'));
  } else if ($expires && $expiry->expiry_schedule->type == 'days') {
    $date->add(new DateInterval('P' . $expiry->expiry_schedule->value . 'D'));
  }

  $json = [
    'expiry' => $expiry,
    'expires' => $expires,
    'date_expires' => $date->format('Y-m-d'),
  ];

} catch (Exception $e) {

}

header('content-type: application/json');
echo json_encode($json);