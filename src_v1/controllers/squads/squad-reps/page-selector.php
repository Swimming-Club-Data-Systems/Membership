<?php

$db = app()->db;
$getSquadCount = $db->prepare("SELECT COUNT(*) FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad AND squadReps.User = ?");
$getSquadCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);
$count = $getSquadCount->fetchColumn();

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $count == 0) {
  // You are a normal parent with no squad rep permissions
  include 'public-rep-list.php';
} else if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $count > 0) {
  // Parent with squad rep permissions
  include 'home.php';
} else {
  // Admin
  include 'home.php';
}