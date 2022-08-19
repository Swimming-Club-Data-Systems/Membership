<?php

$db = app()->db;

$getSquadCount = $db->prepare("SELECT COUNT(*) FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad AND squadReps.User = ?");
$getSquadCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);
$count = $getSquadCount->fetchColumn();

if ($count == 0) {
  halt(404);
}

$squads = $db->prepare("SELECT squads.SquadName, squads.SquadID, squadReps.ContactDescription FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad AND squadReps.User = ?");
$squads->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
]);

$setDesc = $db->prepare("UPDATE squadReps SET ContactDescription = ? WHERE User = ? AND Squad = ?");

while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) {
  if (isset($_POST['field-' . $squad['SquadID']])) {
    $details = null;
    if (mb_strlen(trim($_POST['field-' . $squad['SquadID']])) > 0) {
      $details = mb_strimwidth(trim($_POST['field-' . $squad['SquadID']]), 0, 255);
    }
    $setDesc->execute([
      $details, 
      $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
      $squad['SquadID'],
    ]);
  }
}

http_response_code(302);
header("location: " . autoUrl('squad-reps/contact-details'));