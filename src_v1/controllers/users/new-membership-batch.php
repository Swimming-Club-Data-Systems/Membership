<?php

$db = app()->db;
$tenant = app()->tenant;

// Check user exists
$userInfo = $db->prepare("SELECT Forename, Surname, EmailAddress, Mobile, RR FROM users WHERE Tenant = ? AND UserID = ? AND Active");
$userInfo->execute([
  $tenant->getId(),
  $id
]);

$info = $userInfo->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

// Check how many current or upcoming periods there are
$date = new DateTime('now', new DateTimeZone('Europe/London'));
$getCount = $db->prepare("SELECT COUNT(*) FROM membershipYear WHERE EndDate > ? AND Tenant = ?");
$getCount->execute([
  $date->format('Y-m-d'),
  $tenant->getId(),
]);

$count = $getCount->fetchColumn();

if ($count == 0) {
  //  Nothing to choose

  halt(404);
} else {
  // If one, go straight to it

  http_response_code(302);
  header("location: " . autoUrl("memberships/batches/new?user=" . urlencode($id)));
}
