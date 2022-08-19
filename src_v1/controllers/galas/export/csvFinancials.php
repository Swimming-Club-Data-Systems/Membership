<?php

$db = app()->db;
$tenant = app()->tenant;

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=GalaFeeDetails.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Forename', 'Surname', 'Squad' , 'Swim England Number' , 'Number of Swims' , 'Fee' , 'PaymentType'));

// fetch the data
$sql = $db->query("SELECT members.MForename, members.MSurname, squads.SquadName, members.ASANumber, galaEntries.NumberSwims, galaEntries.Fee, , galaEntries.PaymentTypeID, galaEntries.AmountRefunded FROM (galaEntries (INNER JOIN members ON galaEntries.MemberID = members.MemberID)) ORDER BY `members`.`MForename` , `members`.`MSurname` ASC;");

// loop over the rows, outputting them
while ($row = $sql->fetch(PDO::FETCH_ASSOC)) fputcsv($output, $row);