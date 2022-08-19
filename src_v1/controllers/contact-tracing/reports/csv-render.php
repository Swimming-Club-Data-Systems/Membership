<?php

use function GuzzleHttp\json_decode;

$json = json_decode($json);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . preg_replace('@[^0-9a-z\.]+@i', '-', basename($json->location->name) . '-' . $json->from . '-' . $json->to . '.csv'));

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, ['Name', 'Phone', 'Time (GMT)']);

for ($i = 0; $i < sizeof($json->visitors); $i++) {
  fputcsv($output, [
    $json->visitors[$i]->name,
    $json->visitors[$i]->phone,
    $json->visitors[$i]->time,
  ]);
}