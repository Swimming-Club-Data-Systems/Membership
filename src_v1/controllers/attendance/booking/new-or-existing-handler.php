<?php

$db = app()->db;
$tenant = app()->tenant;

if (!isset($_GET['session']) || !isset($_GET['date'])) {
  halt(404);
}

$getCount = $db->prepare("SELECT COUNT(*) FROM `sessionsBookable` INNER JOIN `sessions` ON `sessions`.`SessionID` = `sessionsBookable`.`Session` WHERE `sessionsBookable`.`Session` = ? AND `sessionsBookable`.`Date` = ? AND `sessions`.`Tenant` = ?");
$getCount->execute([
  $_GET['session'],
  $_GET['date'],
  $tenant->getId(),
]);

if ($getCount->fetchColumn() == 0) {
  include 'require-booking/require-booking.php';
} else {
  include 'book-session/book-session.php';
}