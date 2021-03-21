<?php

require 'FinanceReport.json.php';

$data = json_encode($output);
$data = json_decode($data);
$items = $data->items;

// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=SCDSMembership-FinanceReport.csv');

$fields = ['Date', 'Details', 'In', 'Out', 'Gross/Net', 'Status'];
$types = [];
if (isset($data->types)) {
  $typesData = json_encode($data->types);
  $typesData = json_decode($typesData, true);
  foreach ($data->types as $typeCat => $type) {
    foreach ($type as $id => $name) {
      $types[] = $typeCat . $id;
      $nameText = $name;
      if ($typeCat == 'SquadFee') {
        $nameText .= ' Squad';
      } else if ($typeCat == 'ExtraFee') {
        $nameText .= ' (Extra Fee)';
      }
      $fields[] = $nameText;
    }
  }
}

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, [nezamy_app()->tenant->getKey('CLUB_NAME') . ' Finance Report']);
fputcsv($output, $fields);
foreach ($items as $item) {
  $mainData = [
    $item->date,
    $item->details,
    number_format($item->credits/100, 2, '.', ''),
    number_format($item->debits/100, 2, '.', ''),
    $item->income,
    $item->status
  ];

  // Work out type column
  $typeCols = [];
  if (isset($item->grouping)) {
    foreach ($types as $name) {
      if ($name == $item->grouping->object . $item->grouping->id) {
        // It's this column
        $typeCols[] = number_format(($item->credits - $item->debits)/100, 2, '.', '');
      } else {
        // It's not this column
        $typeCols[] = '';
      }
    }
  }

  $outputLine = array_merge($mainData, $typeCols);

  fputcsv($output, $outputLine);
}