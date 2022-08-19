<?php

$noSquad = false;
$doNotHalt = true;
require 'info.json.php';
$data = json_decode($output);

$squads = null;

$leavers = app()->tenant->getKey('LeaversSquad');
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
  $squads = $db->prepare("SELECT SquadName `name`, SquadID `id` FROM squads WHERE `SquadID` != ? ORDER BY SquadFee DESC, `name` ASC");
  $squads->execute([
    $leavers
  ]);
} else {
  $squads = $db->prepare("SELECT SquadName `name`, SquadID `id` FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad WHERE squadReps.User = ? AND SquadID != ? ORDER BY SquadFee DESC, `name` ASC");
  $squads->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $leavers
  ]);
}

$swimsArray = [
  '25Free' => '25&nbsp;Free',
  '50Free' => '50&nbsp;Free',
  '100Free' => '100&nbsp;Free',
  '200Free' => '200&nbsp;Free',
  '400Free' => '400&nbsp;Free',
  '800Free' => '800&nbsp;Free',
  '1500Free' => '1500&nbsp;Free',
  '25Back' => '25&nbsp;Back',
  '50Back' => '50&nbsp;Back',
  '100Back' => '100&nbsp;Back',
  '200Back' => '200&nbsp;Back',
  '25Breast' => '25&nbsp;Breast',
  '50Breast' => '50&nbsp;Breast',
  '100Breast' => '100&nbsp;Breast',
  '200Breast' => '200&nbsp;Breast',
  '25Fly' => '25&nbsp;Fly',
  '50Fly' => '50&nbsp;Fly',
  '100Fly' => '100&nbsp;Fly',
  '200Fly' => '200&nbsp;Fly',
  '100IM' => '100&nbsp;IM',
  '150IM' => '150&nbsp;IM',
  '200IM' => '200&nbsp;IM',
  '400IM' => '400&nbsp;IM'
];

$swimsTextArray = ['25 Fr', '50 Fr', '100 Fr', '200 Fr', '400 Fr', '800 Fr', '1500 Fr', '25 Bk', '50 Bk', '100 Bk', '200 Bk', '25 Br', '50 Br', '100 Br', '200 Br', '25 Fly', '50 Fly', '100 Fly', '200 Fly', '100 IM', '150 IM', '200 IM', '400 IM'];

$pagetitle = htmlspecialchars($data->squad->name) . " Squad Rep View for " . htmlspecialchars($data->gala->name);

ob_start(); ?>

<!DOCTYPE html>
<html>

<head>
  <meta charset='utf-8'>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <!--<link href="https://fonts.googleapis.com/css?family=Open+Sans:700,700i" rel="stylesheet" type="text/css">-->
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <style>
    table {
      font-size: 9pt;
      /* display: table-cell; */
      table-layout: fixed;
      width: 100%;
      white-space: nowrap;
      margin: 0 0 12pt 0;
    }

    td,
    th {
      width: 14.29mm;
      overflow: hidden;
      text-overflow: ellipsis;
      border: 0.2mm solid #ccc;
      padding: 2pt;
    }
  </style>
  <title>Gala Entry Report</title>
</head>

<body>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

  <p>
    Generated at <?= (new DateTime('now', new DateTimeZone('Europe/London')))->format("H:i \o\\n d/m/Y") ?>
  </p>

  <div class="primary-box mb-3" id="title">
    <h1 class="mb-0">
      <?= htmlspecialchars($data->gala->name) ?>
    </h1>
    <p class="lead mb-0"><?php if ($data->squad->id != "all") { ?><?= htmlspecialchars($data->squad->name) ?> <?php } ?>Gala Entry Report</p>
  </div>

  <h2>How to use this document</h2>
  <p class="lead">
    This document lists all entries by swimmers <?php if ($data->squad->id != "all") { ?>in <?= htmlspecialchars($data->squad->name) ?><?php } else { ?>for <?= htmlspecialchars($data->gala->name) ?><?php } ?>.
  </p>
  <p>
    When printing this document, you may need to select
    <strong>Landscape</strong> if your computer does not do so automatically.
  </p>
  <p>
    *<strong>NT</strong> denotes the swimmer does not have a PB in the given
    category or event.
  </p>

  <div class="avoid-page-break-inside">
    <?php if (app()->tenant->isCLS()) { ?>
      <p>&copy; Chester-le-Street ASC <?= date("Y") ?></p>
    <?php } else { ?>
      <p class="mb-0">&copy; Swimming Club Data Systems <?= date("Y") ?></p>
      <p>Produced by Swimming Club Data Systems for <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></p>
    <?php } ?>
  </div>

  <div class="page-break"></div>

  <?php foreach ($data->entries as $entry) { ?>
    <div class="avoid-page-break-inside">

      <h3><?= htmlspecialchars(\SCDS\Formatting\Names::format($entry->forename, $entry->surname)) ?></h3>

      <div class="row">
        <div class="split-50">
          <p>
            <strong>Swim England Number:</strong> <?= htmlspecialchars($entry->asa_number) ?><br>
            <strong>Age today:</strong> <?= htmlspecialchars($entry->age_today) ?><br>
            <strong>Age on day:</strong> <?= htmlspecialchars($entry->age_on_last_day) ?><br>
            <strong>Age at end of year:</strong> <?= htmlspecialchars($entry->age_at_end_of_year) ?><br>
          </p>
        </div>
        <div class="split-50 text-end">
          <?php if ($entry->charged && isset($entry->payment_intent->id) && $entry->payment_intent->id != null) { ?>
            <p>Paid for by credit/debit card</p>
          <?php } else if ($entry->charged && isset($entry->payment_item->id) && $entry->payment_item->id != null) { ?>
            <p>Paid on account</p>
          <?php } else if ($entry->charged) { ?>
            <p>Paid</p>
          <?php } else { ?>
            <p>Not yet paid</p>
          <?php } ?>
          <p class="mb-0">Total &pound;<?= htmlspecialchars($entry->amount_charged_string) ?></p>
          <p class="mb-0">Refunded &pound;<?= htmlspecialchars($entry->amount_refunded_string) ?></p>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Event</th>
            <?php foreach ($swimsTextArray as $event) { ?>
              <th class="text-center">
                <?= htmlspecialchars($event) ?>
              </th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Entered</td>
            <?php foreach ($entry->events as $event) { ?>
              <td class="text-center">
                <?php if ($event->selected) { ?>YES<?php } else { ?><?php } ?>
              </td>
            <?php } ?>
          </tr>
          <tr>
            <td>Time</td>
            <?php foreach ($entry->events as $event) { ?>
              <td class="text-center">
                <?php if ($event->selected && isset($event->entry_time) && $event->entry_time != null) { ?><?= htmlspecialchars($event->entry_time) ?><?php } else { ?><?php } ?>
              </td>
            <?php } ?>
          </tr>
        </tbody>
      </table>
    </div>
  <?php } ?>


  <?php $landscape = true;
  include BASE_PATH . 'helperclasses/PDFStyles/PageNumbers.php'; ?>
</body>

</html>

<?php

$html = ob_get_clean();

// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

// set font dir here
$options = new Options([
  'fontDir' => getenv('FILE_STORE_PATH') . 'fonts/',
  'fontCache' => getenv('FILE_STORE_PATH') . 'fonts/',
  'isFontSubsettingEnabled' => true,
  'isRemoteEnabled' => true,
  'defaultFont' => 'Open Sans',
  'defaultMediaType' => 'all',
  'isPhpEnabled' => true,
]);
$dompdf->setOptions($options);
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: inline');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$dompdf->stream(str_replace(' ', '', $pagetitle) . ".pdf", ['Attachment' => 0]);
