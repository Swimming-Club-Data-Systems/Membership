<?php

$db = app()->db;
$tenant = app()->tenant;

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=access-keys.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Forename', 'Surname', 'Squads', 'Swim England Number', 'Access Key'));

// fetch the data
$swimmers = $db->prepare("SELECT members.MemberID, members.MForename, members.MSurname, members.ASANumber, members.AccessKey FROM members WHERE members.Tenant = ? ORDER BY `members`.`MForename` , `members`.`MSurname` ASC");
$swimmers->execute([
  $tenant->getId()
]);
$getSquads = $db->prepare("SELECT SquadName FROM squads INNER JOIN squadMembers ON squadMembers.Squad = squads.SquadID WHERE squadMembers.Member = ?");

// loop over the rows, outputting them
while ($row = $swimmers->fetch(PDO::FETCH_ASSOC)) {
  $squads = '';
  $getSquads->execute([
    $row['MemberID']
  ]);
  $doneFirst = false;
  while ($squad = $getSquads->fetch(PDO::FETCH_ASSOC)) {
    if ($doneFirst) {
      $squads .= ', ';
    }
    $doneFirst = true;

    $squads .= $squad['SquadName'];
  }
  fputcsv($output, [
    $row['MForename'],
    $row['MSurname'],
    $squads,
    $row['ASANumber'],
    $row['AccessKey'],
  ]);
}
