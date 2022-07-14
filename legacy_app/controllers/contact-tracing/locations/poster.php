<?php

$doNotHalt = true;


$db = app()->db;
$tenant = app()->tenant;

$getLocation = $db->prepare("SELECT `ID`, `Name`, `Address` FROM covidLocations WHERE `ID` = ? AND `Tenant` = ?");
$getLocation->execute([
  $id,
  $tenant->getId()
]);
$location = $getLocation->fetch(PDO::FETCH_ASSOC);

if (!$location) {
  halt(404);
}

$_SESSION['TENANT-' . app()->tenant->getId()]['qr'][0]['text'] = autoUrl("contact-tracing/check-in/" . $id);
$_SESSION['TENANT-' . app()->tenant->getId()]['qr'][0]['size'] = 1024;
$qrFile = true;

ob_start(); ?>

<!DOCTYPE html>
<html style="padding-bottom: 0; margin-bottom: 0;">

<head>
  <meta charset='utf-8'>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:700,700i" rel="stylesheet" type="text/css"> -->
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

    html,
    body {
      font-size: 16pt;
    }

    h1 {
      font-size: 32pt;
    }

    h2 {
      font-size: 28pt;
    }

    h3 {
      font-size: 24pt;
    }

    h4 {
      font-size: 20pt;
    }

    h5 {
      font-size: 18pt;
    }

    h6 {
      font-size: 16pt;
    }

    address {
      font-size: 12pt;
    }
  </style>
  <title>Location Poster</title>
</head>

<body style="padding-bottom: 0; margin-bottom: 0;">
  <?php

  // Inserts the standard letterhead on PDF pages
  $addr = json_decode(app()->tenant->getKey('CLUB_ADDRESS'));
  $logoPath = null;
  if ($logos = app()->tenant->getKey('LOGO_DIR')) {
    $logoPath = app()->tenant->getFilePath() . 'public/' . mb_substr($logos, 8) . 'logo-1024.png';
  }

  ?>

  <div class=" mb-3">
    <div class="">
      <?php if ($logoPath) { ?>
        <img src="<?= $logoPath ?>" class="logo" style="height: 25mm">
      <?php } else { ?>
        <h1 class="primary"><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></h1>
      <?php } ?>
    </div>
    <div class="" style="position: absolute; top: 0; right: 0;>
      <div style=" text-align:right">
      <img src="<?= BASE_PATH . 'public/img/corporate/scds.png' ?>" style="width: 1cm;">
    </div>
  </div>
  </div>

  <div style="text-align: center;">

    <div class="primary-box mb-3" id="title" style="margin-top: 12pt; padding-top: 24pt; padding-bottom: 24pt;">
      <h1 class="mb-0" style="line-height: 24pt; margin-bottom:12pt;">
        Contact Tracing Check In
      </h1>
      <h1 class="mb-0" style="line-height: 24pt; margin-bottom:20pt;">
        <?= htmlspecialchars($location['Name']) ?>
      </h1>
      <hr style="margin-bottom:18pt;">
      <p class="lead" style="font-size: 20pt; margin-bottom:0pt;">
        You <strong>must</strong> check in at this location.
      </p>
    </div>

    <h2>
      Scan this QR code with your phone
    </h2>

    <?php include BASE_PATH . 'controllers/barcode-generation-system/qr-safe.php'; ?>
    <img width="600" class="qr" src="<?= 'data:image/png;base64,' . base64_encode($qrReturn) ?>" style="width: 6cm; height: 6cm; margin-top:24pt; image-rendering: pixelated;">

    <p>
      And follow the on screen instructions
    </p>

    <h2>
      Or
    </h2>

    <p style="margin: none; padding: none; line-height: 16pt;">
      Go to <strong><?= htmlspecialchars(app('request')->hostname) ?></strong>, find <strong><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></strong>, select <strong>Contact Tracing</strong>, select <strong><?= htmlspecialchars($location['Name']) ?></strong> and follow the on screen instructions
    </p>

  </div>

  <?php $landscape = false;
  // include BASE_PATH . 'helperclasses/PDFStyles/PageNumbers.php'; 
  ?>
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
// $dompdf->setOptions();
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
// $dompdf->set_option('fontDir', getenv('FILE_STORE_PATH') . 'fonts/');
// $dompdf->set_option('fontCache', getenv('FILE_STORE_PATH') . 'fonts/');
// $dompdf->set_option('isFontSubsettingEnabled', true);

// $dompdf->set_option('defaultFont', 'Open Sans');
// $dompdf->set_option('defaultMediaType', 'all');
// $dompdf->set_option("isPhpEnabled", true);
// $dompdf->set_option('isFontSubsettingEnabled', false);
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
$dompdf->stream(preg_replace('@[^0-9a-z\.]+@i', '-', basename($location['Name'])) . ".pdf", ['Attachment' => 0]);
