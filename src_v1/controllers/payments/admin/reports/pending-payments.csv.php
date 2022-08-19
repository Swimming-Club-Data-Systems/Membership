<?php

$db = app()->db;
$tenant = app()->tenant;

$getInfo = $db->prepare("SELECT Forename fn, Surname sn, EmailAddress email, paymentsPending.Name, paymentsPending.Date, Amount, Currency, paymentsPending.Type, paymentsPending.MetadataJSON FROM users INNER JOIN paymentsPending ON users.UserID = paymentsPending.UserID WHERE users.Active AND users.Tenant = ? AND paymentsPending.Status = 'Pending' ORDER BY sn ASC, fn ASC, paymentsPending.Date ASC;");
$getInfo->execute([
  $tenant->getId()
]);

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=pending-payments-data-export.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, [
  'User forename',
  'User surname',
  'User email',
  'Description',
  'Date',
  'Amount paid',
  'Amount credited',
  'Currency',
]);
while ($info = $getInfo->fetch(PDO::FETCH_ASSOC)) {
  $paid = $credited = '0.00';

  if ($info['Type'] == 'Payment') {
    $paid = (string) (\Brick\Math\BigDecimal::of((string) $info['Amount']))->withPointMovedLeft(2)->toScale(2);
  } else if ($info['Type'] == 'Refund') {
    $credited = (string) (\Brick\Math\BigDecimal::of((string) $info['Amount']))->withPointMovedLeft(2)->toScale(2);
  }

  $date = new DateTime($info['dob']);
  fputcsv($output, [
    $info['fn'],
    $info['sn'],
    $info['email'],
    $info['Name'],
    $date->format("d/m/Y"),
    $paid,
    $credited,
    $info['currency'],
  ]);
}