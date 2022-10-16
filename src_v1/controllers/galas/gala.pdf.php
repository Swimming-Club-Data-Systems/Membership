<?php

$db = app()->db;
$tenant = app()->tenant;

$galas = $db->prepare("SELECT GalaName, ClosingDate, GalaDate, GalaVenue, CourseLength, CoachEnters FROM galas WHERE Tenant = ? AND GalaID = ?");
$galas->execute([
  $tenant->getId(),
  $id
]);
$gala = $galas->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

$numEntries = $db->prepare("SELECT COUNT(*) FROM galaEntries WHERE GalaID = ?");
$numEntries->execute([$id]);
$numEntries = $numEntries->fetchColumn();

$amountPaid = $amountLeftToPay = $amountRefunded = $total = 0;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $amountPaidQuery = $db->prepare("SELECT SUM(FeeToPay) FROM galaEntries INNER JOIN members ON members.MemberID = galaEntries.MemberID WHERE GalaID = ? AND Charged = ? AND members.UserID = ?");
  $amountPaidQuery->execute([$id, 1, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $amountPaid = $amountPaidQuery->fetchColumn();
  $amountPaidQuery->execute([$id, 0, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $amountLeftToPay = $amountPaidQuery->fetchColumn();
  $total = $amountPaid + $amountLeftToPay;
  $amountRefunded = $db->prepare("SELECT SUM(AmountRefunded) FROM galaEntries INNER JOIN members ON members.MemberID = galaEntries.MemberID WHERE GalaID = ? AND members.UserID = ?");
  $amountRefunded->execute([$id, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $amountRefunded = $amountRefunded->fetchColumn();
} else {
  $amountPaidQuery = $db->prepare("SELECT SUM(FeeToPay) FROM galaEntries WHERE GalaID = ? AND Charged = ?");
  $amountPaidQuery->execute([$id, 1]);
  $amountPaid = $amountPaidQuery->fetchColumn();
  $amountPaidQuery->execute([$id, 0]);
  $amountLeftToPay = $amountPaidQuery->fetchColumn();
  $total = $amountPaid + $amountLeftToPay;
  $amountRefunded = $db->prepare("SELECT SUM(AmountRefunded) FROM galaEntries WHERE GalaID = ?");
  $amountRefunded->execute([$id]);
  $amountRefunded = $amountRefunded->fetchColumn();
}

$entries = $db->prepare("SELECT * FROM galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID WHERE GalaID = ?");
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  $entries = $db->prepare("SELECT * FROM galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID WHERE GalaID = ? AND UserID = ?");
  $entries->execute([$id, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} else {
  $entries->execute([$id]);
}

$entry = $entries->fetch(PDO::FETCH_ASSOC);

// Arrays of swims used to check whever to print the name of the swim entered
// BEWARE This is in an order to ease inputting data into SportSystems, contrary to these arrays in other files
$swimsArray = GalaEvents::getEvents();

$strokeCounts = [
  'Free' => 0,
  'Back' => 0,
  'Breast' => 0,
  'Fly' => 0,
  'IM' => 0
];
$distanceCounts = [
  '25' => 0,
  '50' => 0,
  '100' => 0,
  '150' => 0,
  '200' => 0,
  '400' => 0,
  '800' => 0,
  '1500' => 0
];
$countEntries = [];
foreach ($swimsArray as $col => $name) {
  $getCount = $db->prepare("SELECT COUNT(*) FROM galaEntries WHERE GalaID = ? AND `" . $col . "` = 1");
  $getCount->execute([$id]);
  $countEntries[$col]['Name'] = $name;
  $countEntries[$col]['Event'] = $col;
  $countEntries[$col]['Stroke'] = preg_replace("/[^a-zA-Z]+/", "", $col);
  $countEntries[$col]['Distance'] = preg_replace("/[^0-9]/", '', $col);
  $countEntries[$col]['Count'] = $getCount->fetchColumn();
  $strokeCounts[$countEntries[$col]['Stroke']] += $countEntries[$col]['Count'];
  $distanceCounts[$countEntries[$col]['Distance']] += $countEntries[$col]['Count'];
}

$pagetitle = $gala['GalaName'] . ' Information';

ob_start(); ?>

<!DOCTYPE html>
<html>

<head>
  <meta charset='utf-8'>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <!--<link href="https://fonts.googleapis.com/css?family=Open+Sans:700,700i" rel="stylesheet" type="text/css">-->
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <title><?= $pagetitle ?></title>
</head>

<body>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

  <div class="row mb-3 text-end">
    <div class="split-50">
    </div>
    <div class="split-50">
      <p>
        <?= date("d/m/Y") ?>
      </p>
    </div>
  </div>

  <div class="primary-box mb-3" id="title">
    <h1 class="mb-0">
      <?= htmlspecialchars($gala['GalaName']) ?>
    </h1>
    <p class="lead mb-0">
      Gala Entry Report
    </p>
  </div>

  <h2>Entries</h2>
  <p>
    There are <?= $numEntries ?> entries to this gala
  </p>

  <?php

  if ($entry != null) { ?>

    <div style="">

      <?php do { ?>
        <div class="">
          <div class="mb-3" style="border: 1pt solid #ddd; background: #fff; padding: 10pt;">
            <div class="row">
              <div class="split-50">
                <h3 style="">
                  <?= htmlspecialchars(\SCDS\Formatting\Names::format($entry['MForename'], $entry['MSurname'])) ?>
                </h3>

                <p class="mb-0" style="">
                  <strong>Year of birth:</strong>&nbsp;<?= date('Y', strtotime($entry['DateOfBirth'])) ?>
                </p>
                <p class="mb-0">
                  <strong>Swim&nbsp;England Number:</strong>&nbsp;<?= htmlspecialchars($entry['ASANumber']) ?>
                </p>
              </div>
              <div class="split-50">
                <table style="border: none;">
                  <tbody style="border: none;">
                    <?php $count = 0; ?>
                    <?php foreach ($swimsArray as $event => $name) { ?>
                      <?php if ($entry[$event]) { ?>
                        <?php if ($count % 2 == 0) { ?>
                          <tr style="border: none;">
                          <?php } ?>
                          <td style="border: none; padding: 0pt;">
                            <?= $name ?>
                          </td>
                          <?php if ($count % 2 == 1) { ?>
                          </tr>
                        <?php } ?>
                        <?php $count++; ?>
                      <?php } ?>
                    <?php } ?>
                    <?php if ($count > 0 && $count % 2 == 0) { ?>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      <?php } while ($entry = $entries->fetch(PDO::FETCH_ASSOC)); ?>

    </div>

  <?php } ?>

  <div class="page-break"></div>

  <img src="<?= BASE_PATH ?>public/img/corporate/scds.png" style="height:1.5cm;" class="mb-3" alt="Swimming Club Data Systems Logo">

  <h2 id="about">Gala Reports</h2>

  <p>&copy; Swimming Club Data Systems <?= date("Y") ?>. Produced for <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>.</p>

  <?php include BASE_PATH . 'helperclasses/PDFStyles/PageNumbers.php'; ?>
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
$dompdf->setPaper('A4', 'portrait');

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
