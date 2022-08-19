<?php

$db = app()->db;
$tenant = app()->tenant;

$latest = $db->prepare("SELECT * FROM `renewals` WHERE Tenant = ? `StartDate` <= CURDATE() AND CURDATE() <= `EndDate` ORDER BY renewals.ID DESC LIMIT 1");
$latest->execute([
  $tenant->getId()
]);
$latestRenewal = $latest->fetch(PDO::FETCH_ASSOC);

$getUserDetails = $db->prepare("SELECT RR FROM users WHERE UserID = ? AND Tenant = ?");
$getUserDetails->execute([
  $person,
  $tenant->getId()
]);
$reg = $getUserDetails->fetchColumn();

$renewal = null;
if ($reg) {
  $renewal = 0;
} else if ($latestRenewal != null) {
  $renewal = $latestRenewal;
} else {
  throw new Exception('No renewal');
}