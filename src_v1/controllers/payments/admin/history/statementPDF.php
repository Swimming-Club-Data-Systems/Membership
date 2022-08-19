<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;
$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$sql = $payments = null;
$count = 0;

// $club = json_decode(CLUB_JSON);
// $addr = json_decode(app()->tenant->getKey('CLUB_ADDRESS'));

// Check the thing exists

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
  // Check the payment exists and belongs to the user
  $sql = $db->prepare("SELECT COUNT(*) FROM payments WHERE PaymentID = ? AND UserID = ?");
  $sql->execute([$id, $user]);
  if ($sql->fetchColumn() == 0) {
    halt(404);
  }

  $sql = $db->prepare("SELECT COUNT(*) FROM `paymentsPending` INNER JOIN `users` ON users.UserID = paymentsPending.UserID WHERE `Payment` = ? AND paymentsPending.UserID = ?");
  $sql->execute([$id, $user]);
  $count = $sql->fetchColumn();

	$payments = $db->prepare("SELECT * FROM `paymentsPending` INNER JOIN `users` ON users.UserID = paymentsPending.UserID WHERE `Payment` = ? AND paymentsPending.UserID = ?");
  $payments->execute([$id, $user]);
} else {
  $sql = $db->prepare("SELECT COUNT(*) FROM payments INNER JOIN users ON users.UserID = payments.UserID WHERE PaymentID = ? AND users.Tenant = ?");
  $sql->execute([
    $id,
    $tenant->getId()
  ]);
  if ($sql->fetchColumn() == 0) {
    halt(404);
  }

  $sql = $db->prepare("SELECT COUNT(*) FROM `paymentsPending` INNER JOIN `users` ON users.UserID = paymentsPending.UserID WHERE `Payment` = ?");
  $sql->execute([$id]);
  $count = $sql->fetchColumn();

	$payments = $db->prepare("SELECT * FROM `paymentsPending` INNER JOIN `users` ON users.UserID = paymentsPending.UserID WHERE `Payment` = ?");
  $payments->execute([$id]);
}

$row = $payments->fetch(PDO::FETCH_ASSOC);

$sql = $db->prepare("SELECT payments.`UserID`, payments.`Name`, `Amount`, `Status`, `Date`, BankName, AccountHolderName, AccountNumEnd, payments.PMKey FROM `payments` LEFT JOIN paymentMandates ON payments.MandateID = paymentMandates.MandateID WHERE `PaymentID` = ?");
$sql->execute([$id]);
$payment_info = $sql->fetch(PDO::FETCH_ASSOC);
$name = getUserName($payment_info['UserID']);

$use_white_background = true;
$PMKey = null;
if ($payment_info['PMKey'] != null) {
  $PMKey = mb_strtoupper($payment_info['PMKey']);
}
$pagetitle = htmlspecialchars("Statement #" . $id);

$_SESSION['TENANT-' . app()->tenant->getId()]['qr'][0]['text'] = autoUrl("payments/statements/" . htmlspecialchars($id));

$billDate = null;
try {
  $billDate = new DateTime($payment_info['Date'], new DateTimeZone('UTC'));
  $billDate->setTimezone(new DateTimeZone('Europe/London'));
} catch (Exception $e) {
  $billDate = new DateTime('now', new DateTimeZone('Europe/London'));
}

$userObj = new \User($payment_info['UserID'], true);
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
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <!--<link href="https://fonts.googleapis.com/css?family=Open+Sans:700,700i" rel="stylesheet" type="text/css">-->
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
          <?=htmlspecialchars($billDate->format("d/m/Y"))?>
        </p>

        <p>
          Internal Reference: <span class="font-monospace">#<?=htmlspecialchars($id)?></span>
        </p>
      </div>
    </div>

    <?php if ($address != null && isset($address->streetAndNumber)) { ?>
    <address class="mb-3 address-font address-box">
      <strong><?=htmlspecialchars($name)?></strong><br>
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
      <strong><?=htmlspecialchars($name)?></strong><br>
      Parent/Carer
    </p>
    <?php } ?>

    <div class="primary-box mb-3" id="title">
      <h1 class="mb-0" title="<?=htmlspecialchars($payment_info['Name'])?>">
        Statement of Fees and Charges
      </h1>
      <p class="lead mb-0">
        <?=htmlspecialchars($payment_info['Name'])?>
      </p>
    </div>

    <h2 id="payment-info">About this payment</h2>
    <dl>
      <div class="row">
        <dt class="split-30">Statement Identifier</dt>
        <dd class="split-70">
          <span class="font-monospace">
            <?=htmlspecialchars($id)?>
          </span>
        </dd>
      </div>

      <div class="row">
        <dt class="split-30">Statement Date</dt>
        <dd class="split-70">
          <span class="font-monospace">
            <?=htmlspecialchars($billDate->format("j F Y"))?>
          </span>
        </dd>
      </div>

      <?php if ($PMKey != null) { ?>
      <div class="row">
        <dt class="split-30">GoCardless Payment Identifier</dt>
        <dd class="split-70">
          <span class="font-monospace">
            <?=htmlspecialchars($PMKey)?>
          </span>
        </dd>
      </div>
      <?php } ?>

      <div class="row">
        <dt class="split-30">Total Fee</dt>
        <dd class="split-70">
          <span class="font-monospace">
            &pound;<?=(string) (\Brick\Math\BigDecimal::of((string) $payment_info['Amount']))->withPointMovedLeft(2)->toScale(2)?>
          </span>
        </dd>
      </div>

      <div class="row">
        <dt class="split-30">Payment Status as of <?=date("d/m/Y")?></dt>
        <dd class="split-70">
          <span class="font-monospace">
            <?=htmlspecialchars(paymentStatusString($payment_info['Status']))?>
          </span>
        </dd>
      </div>

      <?php if ($payment_info['BankName'] != null || $payment_info['AccountNumEnd'] != null || $payment_info['AccountHolderName'] != null) { ?>

      <div class="row">
        <dt class="split-30">Bank</dt>
        <dd class="split-70">
          <span class="font-monospace">
            <?=htmlspecialchars(getBankName($payment_info['BankName']))?>
          </span>
        </dd>
      </div>

      <div class="row">
        <dt class="split-30">Bank Account</dt>
        <dd class="split-70">
          <span class="font-monospace">
            &middot;&middot;&middot;&middot;&middot;&middot;<?=htmlspecialchars($payment_info['AccountNumEnd'])?>
          </span>
        </dd>
      </div>

      <div class="row">
        <dt class="split-30">Account Name</dt>
        <dd class="split-70">
          <span class="font-monospace">
            <?=htmlspecialchars(mb_strtoupper($payment_info['AccountHolderName']))?>
          </span>
        </dd>
      </div>

      <?php } ?>
    </dl>

    <h2 id="payment-details">Itemised Details</h2>
    <p>Payments listed below were charged as part of one single Direct Debit</p>
    <?php if ($count == 0) { ?>
      <div class="">
        <p class="mb-0">
          <strong>
            No fees can be found for this statement
          </strong>
        </p>
        <p class="mb-0">
          This usually means that the payment was created via the GoCardless
          User Interface and not directly in this system. Please speak to the
          treasurer to find out more.
        </p>
      </div>
    <?php } else { ?>
      <table>
        <thead>
          <tr>
            <th>
              Date
            </th>
            <th>
              Description
            </th>
            <th>
              Amount
            </th>
          </tr>
        </thead>
        <tbody>
        <?php
        do {
          //$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
          $data = "";
          if ($row['MetadataJSON'] != "" || $row['MetadataJSON'] != "") {
            $json = json_decode($row['MetadataJSON']);
            if ($json->PaymentType == "SquadFees"  || $json->PaymentType == "ExtraFees") {
              $data .= '<ul class="list-unstyled mb-0">';
              //echo sizeof($json->Members);
              //pre($json->Members);
              //echo $json->Members[0]->MemberName;
              $numMems = (int) sizeof($json->Members);
              for ($y = 0; $y < $numMems; $y++) {
                $data .= '<li>' . htmlspecialchars($json->Members[$y]->FeeName) . " (&pound;" . htmlspecialchars($json->Members[$y]->Fee) . ") for " . htmlspecialchars($json->Members[$y]->MemberName) . '</li>';
              }
              $data .= '</ul>';
            }
          }
          ?>
          <tr>
            <td>
              <?=date("D j M Y",strtotime($row['Date']))?>
            </td>
            <td>
              <?=htmlspecialchars($row['Name'])?>
              <em><?=$data?></em>
            </td>
            <td>
              <?php if ($row['Type'] == "Payment") { ?>
              &pound;<?=(string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2)?>
              <?php } else { ?>
              -&pound;<?=(string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2)?> (Credit)
              <?php } ?>
            </td>
          </tr>
          <?php
          $row = $payments->fetch(PDO::FETCH_ASSOC);
        } while ($row != null); ?>
        </tbody>
      </table>
    <?php } ?>

    <div class="page-break"></div>

    <h2 id="payment-concerns">Got problems or concerns?</h2>
    <p>
      Problems are very rare but when they do happen we'll work to resolve them as quickly as possible.
    </p>
    <p>
      Contact the treasurer(s) as soon as possible if this happens.
    </p>

    <h2 id="payment-questions">Questions about Direct Debit?</h2>
    <p>Full help and support for payments by Direct Debit is available on the <a href="<?= htmlspecialchars(platformUrl('help-and-support')) ?>" target="_blank">Membership System Support Website</a>. Help and Support Documentation is provided by Swimming Club Data Systems to all clubs and users that use this service. If you need somebody to help you, please contact your own club.</p>

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

    <p>&copy; <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> <?=date("Y", strtotime($payment_info['Date']))?></p>

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
