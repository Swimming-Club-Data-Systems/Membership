<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$db = app()->db;
$tenant = app()->tenant;

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$sql = $payments = null;
$count = 0;

$pdfUrl = autoUrl("payments/statements/" . $id . "/pdf");

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

$sql = $db->prepare("SELECT payments.`UserID`, payments.`Name`, `Amount`, `Status`, `Date`, BankName, AccountHolderName, AccountNumEnd, payments.PMKey, payments.stripeMandate, payments.stripeFailureCode FROM `payments` LEFT JOIN paymentMandates ON payments.MandateID = paymentMandates.MandateID WHERE `PaymentID` = ?");
$sql->execute([$id]);
$payment_info = $sql->fetch(PDO::FETCH_ASSOC);
$name = getUserName($payment_info['UserID']);

$use_white_background = true;
$PMKey = null;
if ($payment_info['PMKey'] != null) {
  $PMKey = mb_strtoupper($payment_info['PMKey']);
}
$pagetitle = "Statement for " . htmlspecialchars($name) . ", " . htmlspecialchars("Statement #" . $id);

$_SESSION['TENANT-' . app()->tenant->getId()]['qr'][0]['text'] = autoUrl("payments/statements/" . htmlspecialchars($id));

$billDate = null;
try {
  $billDate = new DateTime($payment_info['Date'], new DateTimeZone('UTC'));
  $billDate->setTimezone(new DateTimeZone('Europe/London'));
} catch (Exception $e) {
  $billDate = new DateTime('now', new DateTimeZone('Europe/London'));
}

$stripeMandateInfo = null;
if ($payment_info['stripeMandate']) {
  $getMandateInfo = $db->prepare("SELECT Last4, SortCode, Reference FROM stripeMandates WHERE ID = ?");
  $getMandateInfo->execute([
    $payment_info['stripeMandate']
  ]);
  $stripeMandateInfo = $getMandateInfo->fetch(PDO::FETCH_ASSOC);
}

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
          <li class="breadcrumb-item"><a href="<?= autoUrl("payments/transactions") ?>">History</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($billDate->format("j M Y")) ?></li>
        </ol>
      </nav>
    <?php } ?>

    <div class="">
      <span class="d-none d-print-block h1"><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Payments</span>
      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") { ?>
        <h1><?= htmlspecialchars($payment_info['Name']) ?> Statement</h1>
      <?php } else { ?>
        <h1>Statement for <?= htmlspecialchars($name) ?></h1>
      <?php } ?>
    </div>
  </div>
</div>

<div class="container-xl">
  <dl class="row">
    <dt class="col-md-4">Statement Identifier</dt>
    <dd class="col-md-8"><span class="font-monospace"><?= htmlspecialchars($id) ?></span></dd>

    <dt class="col-md-4">Statement Date</dt>
    <dd class="col-md-8"><?= htmlspecialchars($billDate->format("j F Y")) ?></dd>

    <?php if ($PMKey != null) { ?>
      <dt class="col-md-4">GoCardless Payment Identifier</dt>
      <dd class="col-md-8"><span class="font-monospace"><?= htmlspecialchars($PMKey) ?></span></dd>
    <?php } ?>

    <dt class="col-md-4">Total Fee</dt>
    <dd class="col-md-8"><span class="font-monospace">&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $payment_info['Amount']))->withPointMovedLeft(2)->toScale(2) ?></span></dd>

    <dt class="col-md-4">Payment Status</dt>
    <dd class="col-md-8"><span class=""><?= htmlspecialchars(paymentStatusString($payment_info['Status'], $payment_info['stripeFailureCode'])) ?></span></dd>

    <?php if ($stripeMandateInfo) { ?>
      <dt class="col-md-4">Sort Code</dt>
      <dd class="col-md-8"><span class="font-monospace"><?= htmlspecialchars(implode("-", str_split($stripeMandateInfo['SortCode'], 2))) ?></span></dd>

      <dt class="col-md-4">Account Number</dt>
      <dd class="col-md-8"><span class="font-monospace">&middot;&middot;&middot;&middot;<?= htmlspecialchars($stripeMandateInfo['Last4']) ?></span></dd>
    <?php } ?>

    <?php if ($payment_info['BankName'] != null || $payment_info['AccountNumEnd'] != null || $payment_info['AccountHolderName'] != null) { ?>

      <dt class="col-md-4">Bank</dt>
      <dd class="col-md-8">
        <span>
          <?= htmlspecialchars(getBankName($payment_info['BankName'])) ?>
        </span>
      </dd>

      <dt class="col-md-4">Bank Account</dt>
      <dd class="col-md-8">
        <span>
          &middot;&middot;&middot;&middot;&middot;&middot;<?= htmlspecialchars($payment_info['AccountNumEnd']) ?>
        </span>
      </dd>

      <dt class="col-md-4">Account Name</dt>
      <dd class="col-md-8">
        <span class="font-monospace">
          <?= htmlspecialchars(mb_strtoupper($payment_info['AccountHolderName'])) ?>
        </span>
      </dd>

    <?php } ?>

  </dl>

  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" && ($payment_info['Status'] == 'customer_approval_denied' || $payment_info['Status'] == 'failed')) {
    $_SESSION['TENANT-' . app()->tenant->getId()]['Token' . $id] = hash('sha256', random_int(0, 999999));
    $url = autoUrl("payments/statements/" . $id . "/mark-paid/" . $_SESSION['TENANT-' . app()->tenant->getId()]['Token' . $id]);
  ?>
    <p>
      <a href="<?= htmlspecialchars($url) ?>" class="btn btn-primary">
        Mark as Paid
      </a>
    </p>
    <p class="text-muted small">
      If this payment failed and/or has been paid manually, by Cash, Cheque or
      Bank Transfer, mark it as paid here.
    </p>
  <?php } ?>

  <div class="mb-4">
    <h2>Itemised Details</h2>
    <p>Payments listed below were charged as part of one single Direct Debit</p>
    <?php if ($count == 0) { ?>
      <div class="alert alert-warning mb-0">
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
      <div class="table-responsive-md">
        <table class="table table-light">
          <thead">
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
                $data = "";
                if ($row['MetadataJSON'] != "" || $row['MetadataJSON'] != "") {
                  $json = json_decode($row['MetadataJSON']);
                  if ($json->PaymentType == "SquadFees"  || $json->PaymentType == "ExtraFees") {
                    $data .= '<ul class="list-unstyled mb-0">';
                    //echo sizeof($json->Members);
                    //pre($json->Members);
                    //echo $json->Members[0]->MemberName;
                    $numMems = 0;
                    if (isset($json->Members) && $json->Members != null) {
                      $numMems = (int) sizeof($json->Members);
                      for ($y = 0; $y < $numMems; $y++) {
                        $data .= '<li>' . htmlspecialchars($json->Members[$y]->FeeName) . " (&pound;" . htmlspecialchars($json->Members[$y]->Fee) . ") for " . htmlspecialchars($json->Members[$y]->MemberName) . '</li>';
                      }
                    }
                    $data .= '</ul>';
                  }
                }
              ?>
                <tr>
                  <td>
                    <?= date("D j M Y", strtotime($row['Date'])) ?>
                  </td>
                  <td>
                    <?= htmlspecialchars($row['Name']) ?>
                    <em><?= $data ?></em>
                  </td>
                  <td>
                    <?php if ($row['Type'] == "Payment") { ?>
                      &pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2) ?>
                    <?php } else { ?>
                      -&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $row['Amount']))->withPointMovedLeft(2)->toScale(2) ?> (Credit)
                    <?php } ?>
                  </td>
                </tr>
              <?php
                $row = $payments->fetch(PDO::FETCH_ASSOC);
              } while ($row != null); ?>
            </tbody>
        </table>
      </div>
    <?php } ?>
  </div>

  <p>
    <a href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank" class="btn btn-primary">
      PDF Download
    </a>
  </p>

  <div class="row">
    <div class="col-md-10 col-lg-8">
      <h2>Got problems or concerns?</h2>
      <p class="lead">
        Problems are very rare but when they do happen we'll work to resolve them
        as quickly as possible.
      </p>

      <h2>Questions about Direct Debit</h2>
      <p>
        Full help and support for payments by Direct Debit is available on the <a href="<?= htmlspecialchars(platformUrl('help-and-support')) ?>" target="_blank">support website</a>.
      </p>
      <p>
        Direct Debit payments to <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> are covered by the Direct Debit Guarantee.
      </p>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
