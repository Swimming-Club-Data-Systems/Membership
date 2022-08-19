<?php

$db = app()->db;

$url_path = "payments";
if ($renewal_trap) {
	$url_path = "renewal/payments";
}

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$date = 1;

try {
  $getPaySchdeule = $db->prepare("SELECT * FROM `paymentSchedule` WHERE `UserID` = ?");
  $getPaySchdeule->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $scheduleExists = $getPaySchdeule->fetch(PDO::FETCH_ASSOC);
  if ($scheduleExists != null) {
  	header("Location: " . autoUrl($url_path . "/setup/2"));
  }
} catch (Exception $e) {
  halt(500);
}

if ($date == null || $date == "") {
	header("Location: " . autoUrl($url_path . "/setup/1"));
} else {
  try {
    $insert = $db->prepare("INSERT INTO `paymentSchedule` (`UserID`, `Day`) VALUES (?, ?)");
    $insert->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $date]);
  	header("Location: " . autoUrl($url_path . "/setup/2"));
  } catch (Exception $e) {
    halt(500);
  }
}
