<?php

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
  $id = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
}

if (!isset($id)) {
  halt(404);
}

$db = app()->db;

$welcome = app()->tenant->getKey('WelcomeLetter');

if ($welcome == null) {
  halt(404);
}

$user = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
$user->execute([$id]);

$userObj = new \User($id, true);
$json = $userObj->getUserOption('MAIN_ADDRESS');
$address = null;
if ($json != null) {
  $address = json_decode($json);
}

$swimmers = $db->prepare("SELECT MForename fn, MSurname sn, SquadName squad, SquadFee fee, SquadCoC exempt, members.MemberID id FROM members INNER JOIN squads ON squads.SquadID = members.SquadID WHERE members.UserID = ? ORDER BY fn ASC");
$swimmers->execute([$id]);
$swimmers = $swimmers->fetchAll(PDO::FETCH_ASSOC);

$email_info = $user->fetch(PDO::FETCH_ASSOC);

$pagetitle = app()->tenant->getKey('CLUB_NAME') . " Welcome Letter";

ob_start();?>

<!DOCTYPE html>
<html>
  <head>
  <meta charset='utf-8'>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i" rel="stylesheet" type="text/css">
  <style>
  .signature-box {
    padding: 5pt;
    margin-bottom: 16pt;
    border: 0.05cm solid #777;
    width: 8cm;
    height: 2cm;
    background: #fff;
  }
  .cell {
    padding: 10pt;
    background: #eee;
    margin: 0 0 16pt 0;
    display: block;
  }
  </style>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <title><?=$pagetitle?></title>
  </head>
  <body>
    <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

    <p class="text-end">
      <?=date("d/m/Y")?>
    </p>

    <?php if ($address != null && isset($address->streetAndNumber)) { ?>
    <address class="mb-3 address-font address-box">
      <strong><?=htmlspecialchars($email_info['Forename'] . " " . $email_info['Surname'])?></strong><br>
      <?=htmlspecialchars($address->streetAndNumber)?><br>
      <?php if (isset($address->flatOrBuilding)) { ?>
      <?=htmlspecialchars($address->flatOrBuilding)?><br>
      <?php } ?>
      <?=htmlspecialchars($address->city)?><br>
      <?=htmlspecialchars(mb_strtoupper($address->postCode))?>
    </address>
    <div class="after-address-box"></div>
    <?php } else { ?>
    <p>
      <strong><?=htmlspecialchars($email_info['Forename'] . " " . $email_info['Surname'])?></strong><br>
      Parent/Carer
    </p>
    <?php } ?>

    <div class="primary-box mb-3" id="title">
      <h1 class="mb-0">
        Welcome to <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>
      </h1>

      <p class="mb-0">
        <strong>This welcome letter covers these swimmer<?php if (sizeof($swimmers) > 1) { ?>s<?php } ?>;</strong>
      </p>

      <ul class="mb-0 list-unstyled"> 
        <?php foreach ($swimmers as $s) { ?>
        <li><?=htmlspecialchars($s['fn'] . ' ' . $s['sn'])?>, <?=htmlspecialchars($s['squad'])?></li>
        <?php } ?>
      </ul>
    </div>

    <?=pdfStringReplace(getPostContent($welcome))?>

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

if (!isset($attachment)) {
  // Output the generated PDF to Browser
  header('Content-Description: File Transfer');
  header('Content-Type: application/pdf');
  header('Content-Disposition: inline');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  $dompdf->stream(str_replace(' ', '', $pagetitle) . ".pdf", ['Attachment' => 0]);
} else if ($attachment) {
  $pdfOutput = $dompdf->output();
}