<?php

$pagetitle = 'Document';

$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

\SCDS\Can::view('TeamManager', $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id);

$query = $db->prepare("SELECT * FROM galas WHERE galas.GalaID = ? AND Tenant = ?");
$query->execute([
  $id,
  $tenant->getId()
]);
$info = $query->fetch(PDO::FETCH_ASSOC);

if ($info == null) {
  halt(404);
}

$dateOfGala = new DateTime($info['GalaDate'], new DateTimeZone('Europe/London'))
;
$lastDayOfYear = new DateTime('last day of December ' . $dateOfGala->format('Y'), new DateTimeZone('Europe/London'))
;
$now = new DateTime('now', new DateTimeZone('Europe/London'));

$getSwimmers = $db->prepare("SELECT MForename first, MSurname last, DateOfBirth dob, Website, Social, Noticeboard, FilmTraining, ProPhoto FROM ((galaEntries INNER JOIN members ON members.MemberID = galaEntries.MemberID) LEFT JOIN memberPhotography ON members.MemberID = memberPhotography.MemberID) WHERE galaEntries.GalaID = ? ORDER BY MForename ASC, MSurname ASC");

$getSwimmers->execute([$id]);

$data = $getSwimmers->fetch(PDO::FETCH_ASSOC);

if ($data == null) {
  halt(404);
}

$swimsArray = ['50Free','100Free','200Free','400Free','800Free','1500Free','50Back','100Back','200Back','50Breast','100Breast','200Breast','50Fly','100Fly','200Fly','100IM','200IM','400IM',];
$swimsTextArray = ['50 Fr','100 Fr','200 Fr','400 Fr','800 Fr','1500 Fr','50 Bk','100 Bk','200 Bk','50 Br','100 Br','200 Br','50 Fly','100 Fly','200 Fly','100 IM','200 IM','400 IM'];

ob_start();

?>

<!DOCTYPE html>
<html>
  <head>
  <meta charset="utf-8">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <!--<link href="https://fonts.googleapis.com/css?family=Open+Sans:700,700i" rel="stylesheet" type="text/css">-->
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>

  <style>
  .list-unstyled {
    list-style: none;
    margin-left: 0;
	  padding-left: 0;
  }
  .text-muted {
    color: #444;
  }
  </style>

  <title><?=htmlspecialchars($info['GalaName'])?> Photography Permissions</title>
  </head>
  <body>
    <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

    <p>
      Generated at <?=$now->format("H:i \o\\n d/m/Y")?>
    </p>

    <div class="primary-box mb-3" id="title">
      <h1 class="mb-0">
        <?=htmlspecialchars($info['GalaName'])?>
      </h1>
      <p class="lead mb-0">Photography Permissions Report</p>
    </div>

    <h2>About this document</h2>
    <p>
      This document lists photography restrictions on members attending this competition. You should take a copy of this form to all galas for coaches and staff to reference, especially if they are regularly posting to social media during a gala.
    </p>

    <p>
      Information correct at <?=$now->format("H:i \o\\n j F Y")?>
    </p>

    <p>
      <strong>Do not distribute this document to unauthorised persons.</strong>
    </p>

    <div class="avoid-page-break-inside">
      <?php if (app()->tenant->isCLS()) { ?>
      <p>&copy; Chester-le-Street ASC <?=date("Y")?></p>
      <?php } else { ?>
      <p class="mb-0">&copy; Swimming Club Data Systems <?=date("Y")?></p>
      <p>Produced by Swimming Club Data Systems for <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></p>
      <?php } ?>
    </div>

    <div class="page-break"></div>

    <div>
    <?php
    do {
      $dob = new DateTime($data['dob'], new DateTimeZone('Europe/London'));
      $age = (int) $dob->diff($now)->format("%Y"); ?>
      <div class="swimmer">
        <h3><?=htmlspecialchars($data['first'] . ' ' . $data['last'])?> <small class="text-muted"><?=$age?></small></h3>
        <?php if ($age >= 18) { ?>
          <p><?=htmlspecialchars($data['first'])?> is an adult so has <strong>no restrictions</strong> on photography.</p>
        <?php } else { ?>
          <?php if (bool($data['Website']) || bool($data['Social']) || bool($data['Noticeboard']) || bool($data['FilmTraining']) || bool($data['ProPhoto'])) { ?>
            <p>You <strong>may</strong> take photos/videos of <?=htmlspecialchars($data['first'])?> for;</p>
            <ul>
              <?php if (bool($data['Website'])) { ?>
                <li>Our website</li>
              <?php } ?>
              <?php if (bool($data['Social'])) { ?>
                <li>Our social media channels</li>
              <?php } ?>
              <?php if (bool($data['Noticeboard'])) { ?>
                <li>Print media</li>
              <?php } ?>
              <?php if (bool($data['FilmTraining'])) { ?>
                <li>Filming for training purposes (swimmer feedback)</li>
              <?php } ?>
              <?php if (bool($data['ProPhoto'])) { ?>
                <li>Professional photographers approved by club may take photos</li>
              <?php } ?>
            </ul>
          <?php } ?>
          <?php if (!bool($data['Website']) || !bool($data['Social']) || !bool($data['Noticeboard']) || !bool($data['FilmTraining']) || !bool($data['ProPhoto'])) { ?>
            <p>You <strong>cannot</strong> take photos/videos of <?=htmlspecialchars($data['first'])?> for;</p>
            <ul>
              <?php if (!bool($data['Website'])) { ?>
                <li>Our website</li>
              <?php } ?>
              <?php if (!bool($data['Social'])) { ?>
                <li>Our social media channels</li>
              <?php } ?>
              <?php if (!bool($data['Noticeboard'])) { ?>
                <li>Print media</li>
              <?php } ?>
              <?php if (!bool($data['FilmTraining'])) { ?>
                <li>Filming for training purposes (swimmer feedback)</li>
              <?php } ?>
              <?php if (!bool($data['ProPhoto'])) { ?>
                <li>Professional photographers approved by club may take photos</li>
              <?php } ?>
            </ul>
          <?php } ?>
        <?php } ?>
      </div>
    <?php } while ($data = $getSwimmers->fetch(PDO::FETCH_ASSOC)); ?>
    </div>

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
