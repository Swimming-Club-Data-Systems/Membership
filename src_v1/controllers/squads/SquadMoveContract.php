<?php

$db = app()->db;
$tenant = app()->tenant;

$sql = "SELECT MoveID, moves.SquadID, squads.SquadName, `MForename`, `MSurname`, Forename, Surname, DateOfBirth, squads.SquadFee, squads.SquadCoC, squads.SquadTimetable, `users`.`UserID`, MovingDate, old.SquadName AS OldSquad, old.SquadFee AS OldFee FROM ((((`members` INNER JOIN `users` ON users.UserID = members.UserID) INNER JOIN `moves` ON members.MemberID = moves.MemberID) INNER JOIN `squads` ON moves.SquadID = squads.SquadID) INNER JOIN squads AS old ON members.SquadID = old.SquadID) WHERE members.MemberID = ? AND members.Tenant = ?";
$email_info = $db->prepare($sql);
$email_info->execute([
  $id,
  $tenant->getId()
]);
$email_info = $email_info->fetch(PDO::FETCH_ASSOC);

$pagetitle = htmlspecialchars($email_info['MForename'] . " " . $email_info['MSurname']) . " Squad Move Contract";

$_SESSION['TENANT-' . app()->tenant->getId()]['qr'][0]['text'] = autoUrl("form-agreement/m/" . urlencode('CodeOfConduct') . '/' . urlencode(date("Y-m-d")) . "/" . urlencode($id) . '/' . urlencode("Squad: " . $email_info['SquadName']));
$_SESSION['TENANT-' . app()->tenant->getId()]['qr'][0]['size'] = 600;
$qrFile = true;

$hasDD = userHasMandates($email_info['UserID']);

$userObj = new \User($email_info['UserID']);
$json = $userObj->getUserOption('MAIN_ADDRESS');
$address = null;
if ($json != null) {
  $address = json_decode($json);
}

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
    border: none;
    background: #eee;
    margin: 0 0 16pt 0;
    display: block;
  }
  .qr {
    image-rendering: pixelated;
  }
  </style>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <title><?=$pagetitle?></title>
  </head>
  <body>
    <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

    <div class="row mb-3 text-end">
      <div class="split-50">
      </div>
      <div class="split-50">
        <p>
          <?=date("d/m/Y")?>
        </p>

        <p>
          Internal Reference: <span class="font-monospace">SM<?=$email_info['MoveID']?></span>
        </p>
      </div>
    </div>

    <?php if ($addr != null && isset($address->streetAndNumber)) { ?>
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
      Registered Parent/Carer
    </p>
    <?php } ?>

    <div class="primary-box mb-3" id="title">
      <h1 class="mb-0">
        Squad move contract
      </h1>
      <p class="lead mb-0">
        <?=htmlspecialchars($email_info['MForename'] . " " . $email_info['MSurname'])?>'s move to <?=htmlspecialchars($email_info['SquadName'])?> Squad
      </p>
    </div>

    <p>
      We're very excited to let you know that <?=htmlspecialchars($email_info['MForename'])?> will be moving from <?=htmlspecialchars($email_info['OldSquad'])?> Squad to <?=htmlspecialchars($email_info['SquadName'])?> Squad on <?=date("l j F Y", strtotime($email_info['MovingDate']))?>. The Squad Fee you will pay will be &pound;<?=number_format($email_info['SquadFee'], 2)?>*.
      <?php if ($email_info['SquadFee'] > $email_info['OldFee']) { ?>
      This is an increase of &pound;<?=number_format($email_info['SquadFee'] - $email_info['OldFee'], 2)?> on your existing squad fee.
      <?php } else if ($email_info['SquadFee'] < $email_info['OldFee']) { ?>
      This is a decrease of &pound;<?=number_format($email_info['OldFee'] - $email_info['SquadFee'], 2)?> on your current squad fee.
      <?php } else { ?>
      This is the same as your current squad fee.
      <?php } ?>
    </p>

    <?php if ($hasDD) { ?>
    <p>
      As you pay by Direct Debit, you won't need to take any action. We'll automatically update your monthly fees.
    </p>
    <?php } else if ($email_info['SquadFee'] != $email_info['OldFee']) { ?>
    <p>
      You will need to manually adjust your monthly payment.
    </p>
    <?php } else { ?>
    <p>
      You won't need to adjust your monthly payment as it's not changing.
    </p>
    <?php } ?>

    <?php if ($email_info['SquadTimetable'] != "" && $email_info['SquadTimetable'] != null) { ?>
    <p>
      You can get the timetable for <?=htmlspecialchars($email_info['SquadName'])?> Squad at <a href="<?=htmlspecialchars($email_info['SquadTimetable'])?>" target="_blank"><?=htmlspecialchars($email_info['SquadTimetable'])?></a>.
    </p>
    <?php } ?>

    <p>
      If you do not think <?=htmlspecialchars($email_info['MForename'])?> will be able to take up their place in <?=htmlspecialchars($email_info['SquadName'])?> Squad, please contact us as soon as possible. We must however warn you that we may not be able keep <?=htmlspecialchars($email_info['MForename'])?> in <?=htmlspecialchars($email_info['OldSquad'])?> Squad if it would prevent us from moving up swimmers in our lower squads.
    </p>

    <?php if ((app()->tenant->isCLS())) { ?>
    <div class="avoid-page-break-inside">
      <div class="d-block">
        <h2>Instructions for parents</h2>
        <p>
          <strong>
            If you've been sent this document electronically, please print it out. Electronic signatures will not be accepted.
          </strong>
        </p>

        <p>
          This document contains a form which your swimmer <strong>must sign before starting in their new squad</strong>.
          <?php if (date_diff(date_create($email_info['DateOfBirth']), date_create('today'))->y < 18) { ?>
          As <?=htmlspecialchars($email_info['MForename'])?> is under the age of 18, you must also sign to confirm that you have explained the content and implications of the code of conduct to <?=htmlspecialchars($email_info['MForename'])?>.
          <?php } ?>
        </p>
      </div>
    </div>

    <div class="avoid-page-break-inside">
      <div class="d-block">
        <h2>Instructions for staff</h2>
        <p>
          <strong>On receipt of this document and having confirmed it has been completed, please scan all QR codes to mark all required forms as completed.</strong> You must be signed in with your club account.
        </p>
      </div>
    </div>
    <?php } ?>

    <p>*<em>Discounts may apply to squad fees</em></p>

    <?php if ($hasDD) { ?>
    <div class="page-break"></div>

    <h1 id="payment-questions">Paying Squad Fees for <?=htmlspecialchars($email_info['SquadName'])?></h1>
    <?php if (app()->tenant->isCLS()) { ?>
    <p>
      Your monthly direct debit will be automatically adjusted accordingly. Payments by Direct Debit are covered by the <a href="#payment-dd-guarantee">Direct Debit Guarantee</a>.
    </p>
    <?php } else { ?>
    <p>
      If your club uses Direct Debit payments, your monthly direct debit will be automatically adjusted accordingly. Payments by Direct Debit are covered by the <a href="#payment-dd-guarantee">Direct Debit Guarantee</a>.
    </p>
    <?php } ?>

    <p>
      Full help and support for payments by Direct Debit is available on the Membership System Support Website at <a href="https://www.chesterlestreetasc.co.uk/support/onlinemembership/">https://www.chesterlestreetasc.co.uk/support/onlinemembership/</a>. Help and Support Documentation is provided by Chester-le-Street ASC<?php if (!(app()->tenant->isCLS())) { ?> to all clubs and users that use this service. If you need somebody to help you, please contact your own club in the first instance<?php } ?>.
    </p>

    <div class="row" id="payment-dd-guarantee">
      <div class="split-75">
        <h2>The Direct Debit Guarantee</h2>
      </div>
      <div class="split-25 text-end">
        <img src="<?=BASE_PATH?>public/img/directdebit/directdebit@3x.png" style="height:1cm;" class="mb-3" alt="Direct Debit Logo">
      </div>
    </div>
    <p>The Direct Debit Guarantee applies to payments made to <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></p>

    <ul>
      <li>
        This Guarantee is offered by all banks and building societies that accept instructions to pay Direct Debits
      </li>
      <li>
        If there are any changes to the amount, date or frequency of your Direct Debit <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> will notify you three working days in advance of your account being debited or as otherwise agreed. If you request <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> to collect a payment, confirmation of the amount and date will be given to you at the time of the request
      </li>
      <li>
        If an error is made in the payment of your Direct Debit, by <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> or your bank or building society, you are entitled to a full and immediate refund of the amount paid from your bank or building society
      </li>
        <ul>
          <li>
            If you receive a refund you are not entitled to, you must pay it back when <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> asks you to
          </li>
        </ul>
      <li>
        You can cancel a Direct Debit at any time by simply contacting your bank or building society. Written confirmation may be required. Please also notify us.
      </li>
    </ul>

    <p>Payments are handled by <a href="https://gocardless.com/">GoCardless</a> on behalf of <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>.</p>

    <!--<p>&copy; <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> <?=date("Y")?></p>-->

    <?php } ?>


    <?php if ($email_info['SquadCoC'] != "" && $email_info['SquadCoC'] != null) { ?>
    <div class="page-break"></div>

    <h1 id="squad-code-of-conduct">
      Code of conduct
    </h1>

    <p>
      By moving into this squad, you must agree to its code of conduct. <?php if ((app()->tenant->isCLS())) { ?><strong>You are required to sign and return this form to the club before <?=date("l j F Y", strtotime($email_info['MovingDate']))?>.<?php } else { ?><strong>If instructed to do so</strong> by your club, you should sign and return this document.<?php } ?>
    </p>

    <p>
      The code of conduct for <?=htmlspecialchars($email_info['SquadName'])?> Squad is as follows;
    </p>

    <?=getPostContent($email_info['SquadCoC'])?>

    <div class="page-break"></div>

    <h1 id="squad-code-of-conduct-agreement">
      Agreement to code of conduct
    </h1>

    <p><strong>
      <?php if ((app()->tenant->isCLS())) { ?>
        Please sign and return this form to any squad coach before you move into this squad on <?=date("l j F Y", strtotime($email_info['MovingDate']))?>. If you've been sent this form by email, please print it out. Electronic signatures will not be accepted.
      <?php } else { ?>
        If required to do so by your club, please sign and return this form.
      <?php } ?>
    </strong></p>

    <div class="prevent-page-break">
      <div class="cell">
        <p>
          <strong>Date of agreement:</strong> On or after <?=date("l j F Y")?>
        </p>

        <?php if (date_diff(date_create($email_info['DateOfBirth']), date_create('today'))->y < 18) { ?>
          <p>
            As <?=htmlspecialchars($email_info['MForename'] . " " . $email_info['MSurname'])?> is under the age of 18 you, their parent/guardian, must sign to confirm you have explained the squad code of conduct to <?=htmlspecialchars($email_info['MForename'])?>.
          </p>

          <p>
            I, <span><?=htmlspecialchars($email_info['Forename'] . " " . $email_info['Surname'])?></span> have explained the content and implications to <?=htmlspecialchars($email_info['MForename'] . " " . $email_info['MSurname'])?> and can confirm that they understood.
          </p>

          <div class="signature-box">
            Signed by <span><?=htmlspecialchars($email_info['Forename'] . " " . $email_info['Surname'])?></span> (or print other name)
          </div>
        <?php } ?>

        <p>
          I, <?=htmlspecialchars($email_info['MForename'] . " " . $email_info['MSurname'])?> agree to the Code of Conduct for <?=htmlspecialchars($email_info['SquadName'])?> Squad as outlined above as required by the Terms and Conditions of Membership of <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>.
        </p>

        <div class="signature-box mb-0">
          Signed by <span><?=htmlspecialchars($email_info['MForename'] . " " . $email_info['MSurname'])?></span>
        </div>
      </div>
    </div>

    <div class="prevent-page-break">
      <div class="cell">
        <div class="row">
          <div class="split-30">
            <?php include BASE_PATH . 'controllers/barcode-generation-system/qr-safe.php'; ?>
            <img width="100" class="qr" src="<?='data:image/png;base64,'.base64_encode($qrReturn)?>">
          </div>
          <div class="split-70">
            <p class="mb-0">
              <strong>
                Staff Use
              </strong>
            </p>
            <p>
              Scan this code to mark form completed.
            </p>
            <p>
              All staff can mark this form as being complete.
          </div>
        </div>
      </div>
    </div>

    <p>
      You must abide by the above code of conduct if you take your place in this squad as per the Membership Terms and Conditions. This new code of conduct may be different to that for your current squad, so please read it carefully.
    </p>
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