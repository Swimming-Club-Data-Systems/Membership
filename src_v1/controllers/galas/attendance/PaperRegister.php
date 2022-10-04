<?php

$db = app()->db;

// GET THE GALA
$getGala = $db->prepare("SELECT GalaName `name` FROM galas WHERE GalaID = ?");
$getGala->execute([
  $id
]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

// HALT IF NOT A GALA
if ($gala == null) {
  halt(404);
}

// GET THE ENTRANTS
$getEntries = $db->prepare("SELECT MForename fn, MSurname sn, SquadName squad FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN squads ON squads.SquadID = members.SquadID) WHERE GalaID = ?");
$getEntries->execute([
  $id
]);

$pagetitle = "Register for " . htmlspecialchars($gala['name']);

ob_start();?>

<!-- 84.6 for name and squad, 100 for remainder -->

<!DOCTYPE html>
<html>
  <head>
  <meta charset='utf-8'>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <!--<link href="https://fonts.googleapis.com/css?family=Open+Sans:700,700i" rel="stylesheet" type="text/css">-->
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <style>
  .item-cell {
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 2mm;
    border: 0.2mm solid #ccc;
    height: 0.5cm;
    margin: 0cm;
    line-height: 0.375cm;
  }

  .name {
    width: 25mm;
  }
  .squad {
    width: 15mm;
  }
  .tick {
    width:6mm;
    text-align: center;
  }
  .border {
    /* border: 0.2mm solid #ccc;
    padding: 0cm;
    margin: 0cm; */
  }

  </style>
  <title><?=htmlspecialchars($gala['name'])?> Register</title>
  </head>
  <body>
    <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

    <p>
      Generated at <?=(new DateTime('now', new DateTimeZone('Europe/London')))->format("H:i \o\\n d/m/Y")?>
    </p>

    <div class="primary-box mb-3" id="title">
      <h1 class="mb-0">
        <?=htmlspecialchars($gala['name'])?>
      </h1>
      <p class="lead mb-0">Attendance register</p>
    </div>

    <p>Paper registers are an interim measure before we introduce online registers for gala sessions. They currently allow you to take a register at up to ten sessions for a gala.</p>

    <div class="border"><div class="item-cell name"><strong>Last</strong></div><div class="item-cell name"><strong>First</strong></div><div class="item-cell squad"><strong>Squad</strong></div><?php for ($i=0; $i < 10; $i++) { ?><div class="item-cell tick"><strong><?=$i+1?></strong></div><?php } ?><?php while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) { ?><div style="display: block;"></div><div class="item-cell name"><?=htmlspecialchars($entry['sn'])?></div><div class="item-cell name"><?=htmlspecialchars($entry['fn'])?></div><div class="item-cell squad"><?=htmlspecialchars($entry['squad'])?></div><?php for ($i=0; $i < 10; $i++) { ?><div class="item-cell tick"></div><?php } ?><?php } ?></div>
      
    <?php include BASE_PATH . 'helperclasses/PDFStyles/PageNumbers.php'; ?>
  </body>
</html>

<?php

$html = ob_get_clean();

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

// set font dir here
$dompdf->set_option('font_dir', BASE_PATH . 'fonts/');

$dompdf->set_option('defaultFont', 'Open Sans');
$dompdf->set_option('defaultMediaType', 'all');
$dompdf->set_option("isPhpEnabled", true);
$dompdf->set_option('isFontSubsettingEnabled', false);
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
