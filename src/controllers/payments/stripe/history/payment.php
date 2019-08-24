<?php

function getWalletName($name) {
  if ($name == 'apple_pay') {
    return 'Apple Pay';
  } else if ($name == 'amex_express_checkout') {
    return 'Amex Express Checkout';
  } else if ($name == 'google_pay') {
    return 'Google Pay';
  } else if ($name == 'masterpass') {
    return 'Masterpass  ';
  } else if ($name == 'samsung_pay') {
    return 'Samsung Pay';
  } else if ($name == 'visa_checkout') {
    return 'Visa Checkout';
  } else {
    return 'Other wallet';
  }
}

function outcomeTypeInfo($type) {
  switch ($type) {
    case 'authorized':
      return '<i class="text-success fa fa-check-circle fa-fw" aria-hidden="true"></i> Payment authorised by issuer';
      break;
    case 'manual_review':
      return 'Requires manual review';
      break;
    case 'issuer_declined':
      return '<i class="text-danger fa fa-times-circle fa-fw" aria-hidden="true"></i> Payment declined by issuer';
      break;
    case 'blocked':
      return '<i class="text-danger fa fa-times-circle fa-fw" aria-hidden="true"></i> Payment blocked by Stripe';
      break;
    case 'invalid':
      return '<i class="text-danger fa fa-times-circle fa-fw" aria-hidden="true"></i> Request details were invalid';
      break;
    default:
      return 'Unknown outcome type';
      break;
  }
}

function outcomeRiskLevel($riskLevel) {
  switch ($riskLevel) {
    case 'normal':
      return '<i class="text-success fa fa-check-circle fa-fw" aria-hidden="true"></i> Normal';
      break;
    case 'elevated':
      return '<i class="text-danger fa fa-info-circle fa-fw" aria-hidden="true"></i> Elevated risk';
      break;
    case '<i class="text-danger fa fa-info-circle fa-fw" aria-hidden="true"></i> highest':
      return 'High risk';
      break;
    case 'not_assessed':
      return '<i class="text-muted fa fa-question-circle fa-fw" aria-hidden="true"></i> Risk not assessed';
      break;
    default:
      return '<i class="text-warning fa fa-info-circle fa-fw" aria-hidden="true"></i> Error in risk evaluation';
      break;
  }
}

function cardCheckInfo($value) {
  switch ($value) {
    case 'pass':
      return '<i class="text-success fa fa-check-circle fa-fw" aria-hidden="true"></i>
       Verified';
      break;
    case 'failed':
      return '<i class="text-danger fa fa-times-circle fa-fw" aria-hidden="true"></i> Check failed';
      break;
    case 'unavailable':
      return '<i class="text-muted fa fa-question-circle fa-fw" aria-hidden="true"></i> Check not possible';
      break;
    case 'unchecked':
      return '<i class="text-muted fa fa-circle fa-fw" aria-hidden="true"></i> Unverified';
      break;
    default:
      return 'Unknown status';
      break;
  }
}

global $db;

$payment = $db->prepare("SELECT * FROM ((stripePayments INNER JOIN stripePaymentItems ON stripePaymentItems.Payment = stripePayments.ID) INNER JOIN users ON stripePayments.User = users.UserID) WHERE stripePayments.ID = ?");
$payment->execute([$id]);

$paymentItems = $db->prepare("SELECT * FROM stripePaymentItems WHERE stripePaymentItems.Payment = ?");
$paymentItems->execute([$id]);

$pm = $payment->fetch(PDO::FETCH_ASSOC);

if ($pm == null || ($_SESSION['AccessLevel'] != 'Admin' && $pm['User'] != $_SESSION['UserID'])) {
  halt(404);
}

\Stripe\Stripe::setApiKey(env('STRIPE'));

$payment = \Stripe\PaymentIntent::retrieve([
  'id' => $pm['Intent'],
  'expand' => ['customer', 'payment_method']
]);

$pagetitle = 'Card Payment #' . htmlspecialchars($id);

include BASE_PATH . 'views/header.php';

$card = null;
if (isset($payment->charges->data[0]->payment_method_details->card)) {
  $card = $payment->charges->data[0]->payment_method_details->card;
}

$date = new DateTime($pm['DateTime'], new DateTimeZone('UTC'));
$date->setTimezone(new DateTimeZone('Europe/London'));

$countries = getISOAlpha2Countries();

?>

<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments/cards")?>">Cards</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments/card-transactions")?>">History</a></li>
      <li class="breadcrumb-item active" aria-current="page">#<?=htmlspecialchars($id)?></li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-8">
      <h1><?php if ($_SESSION['AccessLevel'] == 'Admin') { ?><?=htmlspecialchars($pm['Forename'] . ' ' . $pm['Surname'] . ':')?> <?php } ?>Card payment #<?=htmlspecialchars($id)?></h1>
      <p class="lead">At <?=$date->format("H:i \o\\n j F Y")?></p>

      <?php if ($card != null) { ?>
      <h2>Card information</h2>
      <dl class="row">
        <dt class="col-sm-5 col-md-4">Card</dt>
        <dd class="col-sm-7 col-md-8"><i class="fa <?=htmlspecialchars(getCardFA($card->brand))?>" aria-hidden="true"></i> <span class="sr-only"><?=htmlspecialchars(getCardBrand($card->brand))?></span> &#0149;&#0149;&#0149;&#0149; <?=htmlspecialchars($card->last4)?></dd>

        <dt class="col-sm-5 col-md-4">Type</dt>
        <dd class="col-sm-7 col-md-8"><?=htmlspecialchars(mb_convert_case ($card->funding, MB_CASE_TITLE))?></dd>

        <?php if (isset($card->three_d_secure->authenticated) && $card->three_d_secure->authenticated && isset($card->three_d_secure->succeeded) && $card->three_d_secure->succeeded) { ?>
        <dt class="col-sm-5 col-md-4">Verification</dt>
        <dd class="col-sm-7 col-md-8">Verified using 3D Secure</dd>
        <?php } ?>

        <?php if (isset($card->wallet)) { ?>
        <dt class="col-sm-5 col-md-4">Mobile Wallet Payment</dt>
        <dd class="col-sm-7 col-md-8"><?=getWalletName($card->wallet->type)?></dd>

        <?php if (isset($card->wallet->dynamic_last4)) { ?>
        <dt class="col-sm-5 col-md-4">Device Account Number</dt>
        <dd class="col-sm-7 col-md-8">&#0149;&#0149;&#0149;&#0149; <?=htmlspecialchars($card->wallet->dynamic_last4)?></dd>
        <?php } ?>
        <?php } ?>
      </dl>
        
      <?php if ($_SESSION['AccessLevel'] == 'Admin') { ?>
      <h2>Transaction security information</h2>
      <dl class="row">
        <?php if (isset($payment->charges->data[0]->outcome->risk_level) && $payment->charges->data[0]->outcome->risk_level) { ?>
        <dt class="col-sm-5 col-md-4">Risk level</dt>
        <dd class="col-sm-7 col-md-8"><?=outcomeRiskLevel($payment->charges->data[0]->outcome->risk_level)?></dd>
        <?php } ?>
        
        <?php if (isset($payment->charges->data[0]->outcome->risk_score) && $payment->charges->data[0]->outcome->risk_score) { ?>
        <dt class="col-sm-5 col-md-4">Risk score</dt>
        <dd class="col-sm-7 col-md-8"><?=htmlspecialchars($payment->charges->data[0]->outcome->risk_score)?></dd>
        <?php } ?>

        <?php if (isset($payment->charges->data[0]->outcome->type) && $payment->charges->data[0]->outcome->type) { ?>
        <dt class="col-sm-5 col-md-4">Payment outcome</dt>
        <dd class="col-sm-7 col-md-8"><?=outcomeTypeInfo($payment->charges->data[0]->outcome->type)?></dd>
        <?php } ?>

        <?php if (isset($payment->charges->data[0]->outcome->seller_message) && $payment->charges->data[0]->outcome->seller_message) { ?>
        <dt class="col-sm-5 col-md-4">Status message*</dt>
        <dd class="col-sm-7 col-md-8"><?=htmlspecialchars($payment->charges->data[0]->outcome->seller_message)?></dd>
        <?php } ?>
        
        <?php if (isset($payment->charges->data[0]->receipt_url) && $payment->charges->data[0]->receipt_url) { ?>
        <dt class="col-sm-5 col-md-4">Stripe receipt</dt>
        <dd class="col-sm-7 col-md-8"><a target="_blank" href="<?=htmlspecialchars($payment->charges->data[0]->receipt_url)?>">View receipt</a></dd>
        <?php } ?>
      </dl>

      <p>
        * You must not share status message information with the customer.
      </p>

      <h2>Billing address verification</h2>
      <dl class="row">
        <?php if (isset($payment->charges->data[0]->billing_details->address)) {
          $billingAddress = $payment->charges->data[0]->billing_details->address; ?>
        <dt class="col-sm-5 col-md-4">Billing Address</dt>
        <dd class="col-sm-7 col-md-8">
          <address class="mb-0">
            <?php if (isset($payment->charges->data[0]->billing_details->name)) { ?>
            <strong>
              <?=htmlspecialchars($payment->charges->data[0]->billing_details->name)?>
            </strong><br>
            <?php } ?>
            <?php if (isset($billingAddress->line1)) { ?>
              <?=htmlspecialchars($billingAddress->line1)?><br>
            <?php } ?>
            <?php if (isset($billingAddress->line2)) { ?>
              <?=htmlspecialchars($billingAddress->line2)?><br>
            <?php } ?>
            <?php if (isset($billingAddress->city)) { ?>
              <?=htmlspecialchars($billingAddress->city)?><br>
            <?php } ?>
            <?php if (isset($billingAddress->postal_code)) { ?>
              <?=htmlspecialchars($billingAddress->postal_code)?><br>
            <?php } ?>
            <?php if (isset($billingAddress->state)) { ?>
              <?=htmlspecialchars($billingAddress->state)?><br>
            <?php } ?>
            <?php if (isset($billingAddress->country)) { ?>
              <?=htmlspecialchars($countries[$billingAddress->country])?>
            <?php } ?>
          </address>
        </dd>
        <?php } ?>
        
        <?php if (isset($payment->payment_method->card->checks->address_line1_check) && $payment->payment_method->card->checks->address_line1_check) { ?>
        <dt class="col-sm-5 col-md-4">Address line 1</dt>
        <dd class="col-sm-7 col-md-8"><?=cardCheckInfo($payment->payment_method->card->checks->address_line1_check)?></dd>
        <?php } ?>
        
        <?php if (isset($payment->payment_method->card->checks->address_postal_code_check) && $payment->payment_method->card->checks->address_postal_code_check) { ?>
        <dt class="col-sm-5 col-md-4">Post code</dt>
        <dd class="col-sm-7 col-md-8"><?=cardCheckInfo($payment->payment_method->card->checks->address_postal_code_check)?></dd>
        <?php } ?>
        
        <?php if (isset($payment->payment_method->card->checks->cvc_check) && $payment->payment_method->card->checks->cvc_check) { ?>
        <dt class="col-sm-5 col-md-4">CVC</dt>
        <dd class="col-sm-7 col-md-8"><?=cardCheckInfo($payment->payment_method->card->checks->cvc_check)?></dd>
        <?php } ?>
      </dl>
      <?php } ?>
        
        
      </dl>
      <?php } ?>

      <h2>Payment items</h2>
      <p class="lead">All items in this payment</p>

      <?php if ($item = $paymentItems->fetch(PDO::FETCH_ASSOC)) { ?>
      <ul class="list-group mb-3">
        <?php do { ?>
        <li class="list-group-item">
          <h3><?=htmlspecialchars($item['Name'])?></h3>
          <p><?=htmlspecialchars($item['Description'])?></p>

          <p class="mb-0">&pound;<?=number_format($item['Amount']/100, 2, '.', '')?></p>
        </li>
        <?php } while ($item = $paymentItems->fetch(PDO::FETCH_ASSOC)); ?>
      </ul>
      <?php } ?>

      <?php if (isset($payment->charges->data[0]->amount_refunded) && $payment->charges->data[0]->amount_refunded > 0) { ?>
      <h2>Payment refunds</h2>
      <p>&pound;<?=number_format($payment->charges->data[0]->amount_refunded/100, 2, '.', '')?> refunded to <?=htmlspecialchars(getCardBrand($card->brand))?> **** <?=htmlspecialchars($card->last4)?></p>
      <?php } else { ?>
      <h2>Refund this transaction</h2>
      <p>To refund gala entries, use the gala refunds system.</p>
      <?php } ?>

    </div>
  </div>
</div>

<?php

include BASE_PATH . 'views/footer.php';