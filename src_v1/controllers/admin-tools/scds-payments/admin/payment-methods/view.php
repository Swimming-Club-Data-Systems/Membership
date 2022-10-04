<?php

$db = app()->db;
$tenant = app()->adminCurrentTenant;
$user = app()->adminCurrentUser;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;

$getPaymentMethods = $db->prepare("SELECT tenantPaymentMethods.ID, `MethodID`, `MandateID`, `Type`, `TypeData`, `AcceptanceData`, `MethodDetails`, `Usable`, `Status`, `BillingDetails` FROM tenantPaymentMethods LEFT JOIN tenantPaymentMandates ON tenantPaymentMandates.PaymentMethod = tenantPaymentMethods.MethodID INNER JOIN tenantStripeCustomers ON tenantPaymentMethods.Customer = tenantStripeCustomers.CustomerID WHERE Tenant = ? AND `Usable` AND tenantPaymentMethods.ID = ? ORDER BY Usable DESC, tenantPaymentMethods.Created DESC");
$getPaymentMethods->execute([
  $tenant->getId(),
  $id
]);
$paymentMethod = $getPaymentMethods->fetch(PDO::FETCH_ASSOC);

if (!$paymentMethod) {
  halt(404);
}

$typeData = json_decode($paymentMethod['TypeData']);
$billingDetails = json_decode($paymentMethod['BillingDetails']);

$acceptance = $methodDetails = null;
if ($paymentMethod['AcceptanceData']) {
  $acceptance = json_decode($paymentMethod['AcceptanceData']);
}
if ($paymentMethod['AcceptanceData']) {
  $methodDetails = json_decode($paymentMethod['MethodDetails']);
}

$pagetitle = htmlspecialchars('Payment Method ' . $id);
$methodName = 'Payment Method ' . $id;
$methodDesc = 'Payment Method';
$type = 'unknown';

switch ($paymentMethod['Type']) {
  case 'card':
    $pagetitle = htmlspecialchars(mb_convert_case($typeData->brand, MB_CASE_TITLE) . ' ' . $typeData->last4);
    $methodName = mb_convert_case($typeData->brand, MB_CASE_TITLE) . ' ' . $typeData->last4;
    $methodDesc = mb_convert_case($typeData->funding, MB_CASE_TITLE) . ' card';
    $type = 'card';
    break;
  case 'bacs_debit':
    $pagetitle = htmlspecialchars('Direct Debit Ref: ' . ' ' . $methodDetails->bacs_debit->reference);
    $methodName = 'Reference ' . $methodDetails->bacs_debit->reference;
    $methodDesc = 'Direct Debit Instruction';
    $type = 'direct debit';
    break;
}

$pagetitle .= " - Payments - SCDS";

include BASE_PATH . "views/root/head.php";

?>

<div class="container-xl">

  <?php include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/nav.php'; ?>

  <div class="row align-items-center">
    <div class="col-auto">
      <div class="bg-primary text-white p-4 my-4 d-inline-block rounded">
        <h1 class=""><?= htmlspecialchars($methodName) ?></h1>
        <p class="lead mb-0"><?= htmlspecialchars($methodDesc) ?></p>
      </div>
    </div>
    <aside class="col-auto ms-auto">
      <?php if ($paymentMethod['Type'] == 'card') { ?>
        <img src="<?= htmlspecialchars(autoUrl("img/stripe/" . $typeData->brand . ".png", false)) ?>" srcset="<?= htmlspecialchars(autoUrl("img/stripe/" . $typeData->brand . "@2x.png", false)) ?> 2x, <?= htmlspecialchars(autoUrl("img/stripe/" . $typeData->brand . "@3x.png", false)) ?> 3x" style="height:2.5rem;">
      <?php } else if ($paymentMethod['Type'] == 'bacs_debit') { ?>
        <img style="max-height:50px;" src="<?= htmlspecialchars(autoUrl("img/directdebit/directdebit.png", false)) ?>" srcset="<?= htmlspecialchars(autoUrl("img/directdebit/directdebit@2x.png", false)) ?> 2x, <?= htmlspecialchars(autoUrl("img/directdebit/directdebit@3x.png", false)) ?> 3x" alt="Direct Debit Logo">
      <?php } ?>
    </aside>
  </div>

  <div class="row">
    <div class="col-lg-8">

      <h2>
        Payments with this <?= htmlspecialchars($type) ?>
      </h2>

      <div class="alert alert-info">
        <p class="mb-0">
          <strong>No transaction history is available</strong>
        </p>
        <p class="mb-0">
          This <?= htmlspecialchars($type) ?> has never been used for any payments
        </p>
      </div>

      <h2>
        Billing details
      </h2>

      <address>
        <?php if ($billingDetails->name) { ?>
          <strong><?= htmlspecialchars($billingDetails->name) ?></strong> <br>
        <?php } ?>
        <?php if ($billingDetails->address->line1) { ?>
          <?= htmlspecialchars($billingDetails->address->line1) ?> <br>
        <?php } ?>
        <?php if ($billingDetails->address->line2) { ?>
          <?= htmlspecialchars($billingDetails->address->line2) ?> <br>
        <?php } ?>
        <?php if ($billingDetails->address->city) { ?>
          <?= htmlspecialchars($billingDetails->address->city) ?> <br>
        <?php } ?>
        <?php if ($billingDetails->address->state) { ?>
          <?= htmlspecialchars($billingDetails->address->state) ?> <br>
        <?php } ?>
        <?php if ($billingDetails->address->postal_code) { ?>
          <?= htmlspecialchars($billingDetails->address->postal_code) ?>
        <?php } ?>
      </address>

      <?php if ($billingDetails->email || $billingDetails->phone) { ?>
        <dl class="row">
          <?php if ($billingDetails->email) { ?>
            <dt class="col-sm-3">
              Email
            </dt>
            <dd class="col-sm-9">
              <a href="<?= htmlspecialchars('mailto:' . $billingDetails->email) ?>"><?= htmlspecialchars($billingDetails->email) ?></a>
            </dd>
          <?php } ?>
          <?php if ($billingDetails->phone) {
            $phone = null;
            try {
              $phone = PhoneNumber::parse($billingDetails->phone);
            } catch (PhoneNumberParseException $e) {
              $phone = false;
            }
            if ($phone) { ?>
              <dt class="col-sm-3">
                Phone
              </dt>
              <dd class="col-sm-9">
                <a href="<?= htmlspecialchars($phone->format(PhoneNumberFormat::RFC3966)) ?>"><?= htmlspecialchars($phone->format(PhoneNumberFormat::INTERNATIONAL)) ?></a>
              </dd>
          <?php }
          } ?>
        </dl>
      <?php } ?>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\RootFooter();
$footer->render();

?>