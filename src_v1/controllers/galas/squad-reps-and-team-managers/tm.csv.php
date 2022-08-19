<?php

require 'tm.json.php';

$data = json_decode($output);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=SCDSMembership-GalaEntriesInformationReport.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

fputcsv($output, [app()->tenant->getKey('CLUB_NAME') . ' Team Manager Gala Entry Report']);
fputcsv($output, ['Swimmer', 'Age Now', 'Age Last Day', 'Age EoY', 'Row Type', '25 Free', '50 Free', '100 Free', '200 Free', '400 Free', '800 Free', '1500 Free', '25 Back', '50 Back', '100 Back', '200 Back', '25 Breast', '50 Breast', '100 Breast', '200 Breast', '25 Fly', '50 Fly', '100 Fly', '200 Fly', '100 IM', '150 IM', '200 IM', '400 IM']);

foreach ($data->entries as $entry) {
  $swimmerRow = $swimmerTimeRow = [];
  $swimmerRow[] = \SCDS\Formatting\Names::format($entry->forename, $entry->surname);
  $swimmerRow[] = $entry->age_today;
  $swimmerRow[] = $entry->age_on_last_day;
  $swimmerRow[] = $entry->age_at_end_of_year;

  $swimmerTimeRow[] = $swimmerTimeRow[] = $swimmerTimeRow[] = $swimmerTimeRow[] = '';

  $swimmerRow[] = 'Selected swims';
  //$swimmerTimeRow[] = '';
  $swimmerTimeRow[] = 'Entry times';
  foreach ($entry->events as $event) {
    if ($event->selected) {
      $swimmerRow[] = '✓';
    } else {
      $swimmerRow[] = '';
    }
    $swimmerTimeRow[] = $event->entry_time;
  }
  fputcsv($output, $swimmerRow);
  fputcsv($output, $swimmerTimeRow);
}
