<?php

$db = app()->db;
$tenant = app()->tenant;

$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends`, CoachEnters FROM galas WHERE GalaID = ? AND Tenant = ?");
$galaDetails->execute([
  $id,
  $tenant->getId()
]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

$squads = $db->query("SELECT SquadName `name`, SquadID `id` FROM squads ORDER BY SquadFee DESC, SquadName ASC;");

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$session = $getSessions->fetch(PDO::FETCH_ASSOC);

$pagetitle = 'Invite parents to enter ' . htmlspecialchars($gala['name']);

include BASE_PATH . 'views/header.php';

while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) {
  $_POST['squad-' . $squad['id']];
}