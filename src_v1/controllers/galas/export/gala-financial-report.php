<?php

$db = app()->db;
$tenant = app()->tenant;

if (!app()->user->hasPermissions(['Admin', 'Galas'])) halt(404);

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=GalaFeeDetails.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Get gala
$getGala = $db->prepare("SELECT GalaName FROM galas WHERE GalaID = ? AND Tenant = ?");
$getGala->execute([
  $id,
  $tenant->getId(),
]);

$gala = $getGala->fetchColumn();

if (!$gala) halt(404);

// output the column headings
fputcsv($output, [$gala . ' - Report']);
fputcsv($output, ['Surname', 'Forename', 'Swim England Number' , 'Fee' , 'Refunded']);

// fetch the data
$getData = $db->prepare("SELECT MForename, MSurname, ASANumber, galaEntries.FeeToPay, galaEntries.Charged, galaEntries.AmountRefunded FROM members INNER JOIN galaEntries ON members.MemberID = galaEntries.MemberID WHERE GalaID = ? AND members.Tenant = ? ORDER BY MSurname ASC, MForename ASC");
$getData->execute([
  $id,
  $tenant->getId(),
]);

// loop over the rows, outputting them
while ($row = $getData->fetch(PDO::FETCH_OBJ)) {
  // getData
  $feeToPay = number_format($row->FeeToPay, 2, '.', '');
  $refunded = MoneyHelpers::intToDecimal($row->AmountRefunded);
  fputcsv($output, [
    $row->MSurname,
    $row->MForename,
    $row->ASANumber,
    $feeToPay,
    $refunded,
  ]);
}