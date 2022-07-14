<?php

$db = app()->db;
$tenant = app()->tenant;

$query = null;

$markdown = new ParsedownExtra();

// Safe mode is disabled during the transition to markdown
// $markdown->setSafeMode(true);

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $sql = "SELECT COUNT(*) FROM `members` WHERE `UserID` = ?";
	try {
		$query = $db->prepare($sql);
		$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
    if ($query->fetchColumn() == 0) {
      halt(404);
    }
	} catch (PDOException $e) {
		halt(500);
	}
}

$sql = "SELECT * FROM `posts` WHERE `ID` = ? AND Tenant = ?";
try {
  $query = $db->prepare($sql);
  $query->execute([
    $id,
    $tenant->getId()
  ]);
} catch (PDOException $e) {
  halt(500);
}
$row = $query->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  halt(404);
}

$date = new DateTime($row['Modified'], new DateTimeZone('Europe/London'));
	
ob_start();?>

<!DOCTYPE html>
<html>
  <head>
  <meta charset='utf-8'>
  <?php if (app()->tenant->isCLS()) { ?>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <?php } else { ?>
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i" rel="stylesheet" type="text/css">
  <?php } ?>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <title><?= htmlentities($row['Title']) ?></title>
  </head>
  <body>
    <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

		<div class="row mb-3 text-end">
      <div class="split-50">
      </div>
      <div class="split-50">
        <p>
          <?=$date->format("d/m/Y")?>
        </p>
      </div>
    </div>

    <div class="" id="title">
      <h1>
				<?=htmlspecialchars($row['Title'])?>
      </h1>
    </div>

    <?=$markdown->text($row['Content'])?>

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
  'defaultFont' => 'Source Sans Pro',
  'defaultMediaType' => 'all',
  'isPhpEnabled' => true,
  'isHtml5ParserEnabled' => true,
]);
$dompdf->setOptions($options);
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

if (!isset($attachment)) {
  // Output the generated PDF to Browser
  header('Content-Description: File Transfer');
  header('Content-Type: application/pdf');
  header('Content-Disposition: inline');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  $dompdf->stream(str_replace(' ', '', $row['Title']) . ".pdf", ['Attachment' => 0]);
} else if ($attachment) {
  $pdfOutput = $dompdf->output();
}
