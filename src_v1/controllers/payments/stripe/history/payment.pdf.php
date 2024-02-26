<?php

halt(404);

function outcomeTypeInfo($type)
{
  return match ($type) {
      'authorized' => 'Payment authorised by issuer',
      'manual_review' => 'Requires manual review',
      'issuer_declined' => 'Payment declined by issuer',
      'blocked' => 'Payment blocked by Stripe',
      'invalid' => 'Request details were invalid',
      default => 'Unknown outcome type',
  };
}

function outcomeRiskLevel($riskLevel)
{
  return match ($riskLevel) {
      'normal' => 'Normal',
      'elevated' => 'Elevated risk',
      'highest' => 'High risk',
      'not_assessed' => 'Risk not assessed',
      default => 'Error in risk evaluation',
  };
}

function cardCheckInfo($value)
{
  return match ($value) {
      'pass' => 'Verified',
      'failed' => 'Check failed',
      'unavailable' => 'Check not possible',
      'unchecked' => 'Unverified',
      default => 'Unknown status',
  };
}

function paymentIntentStatus($value)
{
  return match ($value) {
      'requires_payment_method' => 'Requires payment method',
      'requires_confirmation' => 'Requires confirmation',
      'requires_action' => 'Requires action',
      'processing' => 'Processing',
      'requires_capture' => 'Required capture',
      'canceled' => 'Cancelled',
      'succeeded' => 'Succeeded',
      default => 'Unknown status',
  };
}

$db = app()->db;

$payment = $db->prepare("SELECT * FROM ((stripePayments LEFT JOIN stripePaymentItems ON stripePaymentItems.Payment = stripePayments.ID) INNER JOIN users ON stripePayments.User = users.UserID) WHERE stripePayments.ID = ?");
$payment->execute([$id]);

$paymentItems = $db->prepare("SELECT * FROM stripePaymentItems WHERE stripePaymentItems.Payment = ?");
$paymentItems->execute([$id]);

$pm = $payment->fetch(PDO::FETCH_ASSOC);

if ($pm == null || ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Admin' && $pm['User'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
  halt(404);
}

\Stripe\Stripe::setApiKey(getenv('STRIPE'));

$payment = \Stripe\PaymentIntent::retrieve([
  'id' => $pm['Intent'],
  'expand' => ['customer', 'payment_method']
]);

$card = null;
if (isset($payment->charges->data[0]->payment_method_details->card)) {
  $card = $payment->charges->data[0]->payment_method_details->card;
}

$date = new DateTime($pm['DateTime'], new DateTimeZone('UTC'));
$date->setTimezone(new DateTimeZone('Europe/London'));

$pagetitle = 'Payment Receipt SPM' . htmlspecialchars((string) $id);

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
        <?= $date->format("d/m/Y") ?>
      </p>

      <p>
        Internal Reference: <span class="font-monospace">SPM<?= htmlspecialchars((string) $id) ?></span>
      </p>

      <p>
        For help contact us via<br>
        <?= htmlspecialchars((string) app()->tenant->getKey('CLUB_EMAIL')) ?>
      </p>
    </div>
  </div>

  <p>
    <strong><?php if (isset($payment->charges->data[0]->billing_details->name)) { ?><?= htmlspecialchars((string) $payment->charges->data[0]->billing_details->name) ?><?php } else { ?><?= htmlspecialchars((string) \SCDS\Formatting\Names::format($pm['Forename'], $pm['Surname'])) ?><?php } ?></strong><br>
    Cardholder
  </p>

  <div class="primary-box mb-3" id="title">
    <h1 class="mb-0" title="Payment Receipt">
      Payment receipt
    </h1>
  </div>

  <p>
    Thank you for your payment to <?= htmlspecialchars((string) app()->tenant->getKey('CLUB_NAME')) ?>.
  </p>

  <p>
    In accordance with card network rules, refunds for gala rejections will only be made to the payment card which was used.
  </p>

  <p>
    Should you wish to withdraw your swimmers you will need to contact the gala coordinator. Depending on the gala and host club, you may not be eligible for a refund in such circumstances unless you have a reason which can be evidenced, such as a doctors note.
  </p>

  <hr>

  <!--<h2 id="payment-details">Items</h2>-->
  <?php if ($item = $paymentItems->fetch(PDO::FETCH_ASSOC)) { ?>
    <dl>
      <?php
      do { ?>
        <div class="row">
          <dt class="split-50"><?= htmlspecialchars((string) $item['Name']) ?><br><?= htmlspecialchars((string) $item['Description']) ?></dt>
          <dd class="split-50">
            <span class="font-monospace">
              &pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $item['Amount']))->withPointMovedLeft(2)->toScale(2) ?>
            </span>
          </dd>
        </div>
      <?php } while ($item = $paymentItems->fetch(PDO::FETCH_ASSOC)); ?>
    </dl>
  <?php } else { ?>
    <div class="">
      <p class="mb-0">
        <strong>
          No fees can be found for this payment
        </strong>
      </p>
      <p class="mb-0">
        This usually means that the payment was created in another system. Please speak to the
        treasurer to find out more.
      </p>
    </div>
  <?php } ?>

  <hr>

  <dl>
    <div class="row">
      <dt class="split-50"><strong>Total</strong></dt>
      <dd class="split-50">
        <span class="font-monospace">
          &pound;<?= number_format($pm['Amount'] / 100, 2, '.', '') ?>
        </span>
      </dd>
    </div>
  </dl>

  <hr>

  <!--<h2 id="payment-info">Details</h2>-->
  <dl>
    <div class="row">
      <dt class="split-50">Amount</dt>
      <dd class="split-50">
        <span class="font-monospace">
          &pound;<?= number_format($payment->amount / 100, 2, '.', '') ?>
        </span>
      </dd>
    </div>

    <?php if ($card != null) { ?>
      <div class="row">
        <dt class="split-50">Card</dt>
        <dd class="split-50">
          <span class="font-monospace">
            <?= htmlspecialchars((string) getCardBrand($card->brand)) ?> <?= htmlspecialchars((string) $card->funding) ?> card<br>
            **** **** **** <?= htmlspecialchars((string) $card->last4) ?>
          </span>
        </dd>
      </div>
    <?php } ?>

    <?php if (isset($card->three_d_secure->authenticated) && $card->three_d_secure->authenticated && isset($card->three_d_secure->succeeded) && $card->three_d_secure->succeeded) { ?>

      <div class="row">
        <dt class="split-50">Verification</dt>
        <dd class="split-50">
          <span class="font-monospace">
            Verified using 3D Secure
          </span>
        </dd>
      </div>

    <?php } ?>

    <?php if (isset($card->wallet)) { ?>
      <div class="row">
        <dt class="split-50">Mobile wallet</dt>
        <dd class="split-50">
          <span class="font-monospace">
            <?= getWalletName($card->wallet->type) ?>
          </span>
        </dd>
      </div>

      <?php if (isset($card->wallet->dynamic_last4)) { ?>
        <div class="row">
          <dt class="split-50">Device account number</dt>
          <dd class="split-50">
            <span class="font-monospace">
              **** **** **** <?= htmlspecialchars((string) $card->wallet->dynamic_last4) ?>
            </span>
          </dd>
        </div>
      <?php } ?>
    <?php } ?>

    <?php if (isset($payment->charges->data[0]->outcome->type) && $payment->charges->data[0]->outcome->type) { ?>
      <div class="row">
        <dt class="split-50">Outcome</dt>
        <dd class="split-50">
          <span class="font-monospace">
            <?= outcomeTypeInfo($payment->charges->data[0]->outcome->type) ?>
          </span>
        </dd>
      </div>
    <?php } ?>
  </dl>

  <dl>
    <?php if (isset($payment->charges->data[0]->billing_details->address)) {
      $billingAddress = $payment->charges->data[0]->billing_details->address;
    ?>
      <div class="row">
        <dt class="split-50">Billing address</dt>
        <dd class="split-50">
          <span class="font-monospace">
            <address class="mb-0">
              <?php if (isset($payment->charges->data[0]->billing_details->name)) { ?>
                <?= htmlspecialchars((string) $payment->charges->data[0]->billing_details->name) ?>
                <br>
              <?php } ?>
              <?php if (isset($billingAddress->line1) && $billingAddress->line1 != null) { ?>
                <?= htmlspecialchars((string) $billingAddress->line1) ?><br>
              <?php } ?>
              <?php if (isset($billingAddress->line2) && $billingAddress->line2 != null) { ?>
                <?= htmlspecialchars((string) $billingAddress->line2) ?><br>
              <?php } ?>
              <?php if (isset($billingAddress->city) && $billingAddress->city != null) { ?>
                <?= htmlspecialchars((string) $billingAddress->city) ?><br>
              <?php } ?>
              <?php if (isset($billingAddress->postal_code) && $billingAddress->postal_code != null) { ?>
                <?= htmlspecialchars((string) $billingAddress->postal_code) ?><br>
              <?php } ?>
              <?php if (isset($billingAddress->state) && $billingAddress->state != null) { ?>
                <?= htmlspecialchars((string) $billingAddress->state) ?><br>
              <?php } ?>
              <?php if (isset($billingAddress->country) && $billingAddress->country != null) { ?>
                <?= htmlspecialchars((string) $countries[$billingAddress->country]) ?>
              <?php } ?>
            </address>
          </span>
        </dd>
      </div>
    <?php } ?>

  </dl>

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
