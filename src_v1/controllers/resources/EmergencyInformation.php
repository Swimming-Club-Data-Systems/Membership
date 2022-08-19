<?php

$db = app()->db;
$tenant = app()->tenant;
$currentUser = app()->user;

$squads = $db->prepare("SELECT SquadID, SquadName FROM squads AND Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$squads->execute([
  $tenant->getId()
]);

$swimmers = $db->prepare("SELECT MForename fn, MSurname sn, Website, Social, Noticeboard, FilmTraining, ProPhoto, Conditions, Allergies, Medication, Mobile FROM (((members LEFT JOIN memberMedical ON members.MemberID = memberMedical.MemberID) LEFT JOIN memberPhotography ON members.MemberID = memberPhotography.MemberID) LEFT JOIN users ON members.UserID = users.UserID) WHERE members.SquadID = ?");

ob_start();

?>

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

    .avoid-page-break-inside {
      page-break-inside: avoid;
    }

    .d-inline {
      display: inline;
    }

    .d-block {
      display: block;
    }

    .pb-3 {
      padding-bottom: 12pt;
    }

    .text-center {
      text-align: center;
    }
  </style>
  <title>Emergency Registers and Essential Information</title>
</head>

<body>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

  <p>
    Generated at <?= date("H:i \o\\n d/m/Y") ?> by <?= htmlspecialchars($currentUser->getName()) ?>
  </p>

  <!--
    <p>
      <strong><?= $name ?></strong><br>
      Parent
    </p>
    -->

  <div class="primary-box mb-3" id="title">
    <h1>
      Emergency Squad Registers and Essential Information
    </h1>
    <p class="lead mb-0">
      For emergency use only
    </p>
  </div>

  <h2>How to use this document</h2>
  <p class="lead">
    This document contains emergency squad registers, medical information, photography permissions and emergency contact details.
  </p>

  <div class="avoid-page-break-inside">
    <?php if (app()->tenant->isCLS()) { ?>
      <p>&copy; Chester-le-Street ASC <?= date("Y") ?></p>
    <?php } else { ?>
      <p class="mb-0">&copy; Swimming Club Data Systems <?= date("Y") ?></p>
      <p>Produced by Swimming Club Data Systems for <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></p>
    <?php } ?>
  </div>

  <?php while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) {

    $swimmers->execute([$squad['SquadID']]);

  ?>
    <div class="page-break"></div>

    <div class="mb-3">
      <h2><?= htmlspecialchars($squad['SquadName']) ?></h2>
      <table>
        <thead>
          <tr>
            <td>
              Name
            </td>
            <td>
              Present
            </td>
            <td>
              Information
            </td>
          </tr>
        </thead>
        <tbody>
          <?php while ($swimmer = $swimmers->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
              <td>
                <?= htmlspecialchars(\SCDS\Formatting\Names::format($swimmer['fn'], $swimmer['sn'])) ?>
              </td>
              <td>
              </td>
              <td>
                Member Info
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  <?php } ?>

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
