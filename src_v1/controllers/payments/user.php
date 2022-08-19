<?php

$db = app()->db;
$tenant = app()->tenant;

// require 'GoCardlessSetup.php';

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Payments and Direct Debits";

// Get mandates
$stripeCusomer = app()->user->getStripeCustomer();
$getMandates = $db->prepare("SELECT ID, Mandate, Last4, SortCode, `Address`, Reference, `URL`, `Status` FROM stripeMandates WHERE Customer = ? AND (`Status` = 'accepted' OR `Status` = 'pending') ORDER BY CreationTime DESC LIMIT 1");
if ($stripeCusomer) {
  $getMandates->execute([
    $stripeCusomer->id,
  ]);
}
$mandate = $getMandates->fetch(PDO::FETCH_ASSOC);

$balance = getAccountBalance($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']);

$use_white_background = true;
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<!--
<div class="bg-warning box-shadow mb-3 py-2" style="margin-top:-1rem;">
 <div class="<?= $container_class ?>">
   <nav class="nav nav-underline">
     <strong>
       Remember to cancel your Standing Order for Monthly Fees
     </strong>
   </nav>
 </div>
</div>
-->

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Payments</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-md-6 col-lg-8">
        <h1>Payments</h1>
        <p class="lead mb-0">Manage your payments to <?= htmlspecialchars(app()->tenant->getName()) ?></p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-md-8">
      <div class="cell">
        <h2>Billing History</h2>
        <p class="lead">Previous payments by Direct Debit</p>
        <?= paymentHistory(null, $user) ?>
      </div>
      <div class="cell">
        <h2>
          Extra Fees this month
        </h2>
        <p class="lead">Fees to pay on your next billing date, in addition to squad fees</p>
        <?= feesToPay(null, $user) ?>
      </div>
    </div>
    <div class="col-md-4">
      <?php if ($tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') || $tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) { ?>
        <div class="cell">
          <h2 class="">
            My Bank Account
          </h2>
          <p class="lead font-italic">
            (New Direct Debit system)
          </p>

          <?php if ($mandate) { ?>
            <dl class="row">
              <dt class="col-sm-5">Sort code</dt>
              <dd class="col-sm-7 font-monospace"><?= htmlspecialchars(implode("-", str_split($mandate['SortCode'], 2))) ?></dd>

              <dt class="col-sm-5">Account number</dt>
              <dd class="col-sm-7 font-monospace">&middot;&middot;&middot;&middot;<?= htmlspecialchars($mandate['Last4']) ?></dd>

              <dt class="col-sm-5">Payment reference</dt>
              <dd class="col-sm-7 font-monospace"><?= htmlspecialchars($mandate['Reference']) ?></dd>
            </dl>
          <?php } ?>

          <div class="d-grid gap-2">
          <?php if ($mandate) { ?>
            <a href="<?= autoUrl("payments/direct-debit") ?>" class="btn btn-dark-l btn-outline-light-d">Manage your bank account</a>
          <?php } else { ?>
            <a href="<?= autoUrl("payments/direct-debit/set-up") ?>" class="btn btn-dark-l btn-outline-light-d">Setup a Direct Debit</a>
          <?php } ?>
          </div>
        </div>
      <?php } ?>
      <?php if ($tenant->getKey('GOCARDLESS_ACCESS_TOKEN') && userHasMandates($user)) { ?>
        <div class="cell">
          <h2 class="mb-3">
            My Bank Account<?php if ($tenant->getBooleanKey('ALLOW_STRIPE_DIRECT_DEBIT_SET_UP') || $tenant->getBooleanKey('USE_STRIPE_DIRECT_DEBIT')) { ?> (Legacy)<?php } ?>
          </h2>
          <?php
          $name = mb_strtoupper(bankDetails($user, "account_holder_name"));
          if ($name != "UNKNOWN") {
            $name = $name . ', ';
          } else {
            $name = null;
          }
          $bank = mb_strtoupper(bankDetails($user, "bank_name"));
          $logo_path = getBankLogo($bank);
          ?>
          <?php if ($logo_path) { ?>
            <img class="img-fluid mb-3" style="max-height:35px;" src="<?= $logo_path ?>.png" srcset="<?= $logo_path ?>@2x.png 2x, <?= $logo_path ?>@3x.png 3x">
          <?php } ?>
          <p class="mb-0"><?= htmlspecialchars($name) ?><abbr title="<?= htmlspecialchars(strtoupper(bankDetails($user, "bank_name"))) ?>"><?= htmlspecialchars(getBankName(bankDetails($user, "bank_name"))) ?></abbr></p>
          <p class="font-monospace">&middot;&middot;&middot;&middot;&middot;&middot;<?= htmlspecialchars(strtoupper(bankDetails($user, "account_number_end"))) ?></p>
          <p><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> does not store your bank details.</p>
          <p class="mb-0">
          <div class="d-grid gap-2">
            <?php if (userHasMandates($user)) { ?>
              <a href="<?= autoUrl("payments/mandates") ?>" class="btn btn-dark-l btn-outline-light-d">Manage your bank account</a>
            <?php } else { ?>
              <a href="<?= autoUrl("payments/setup") ?>" class="btn btn-dark-l btn-outline-light-d">Setup a Direct Debit</a>
            <?php } ?>
          </div>
          </p>
        </div>
      <?php } ?>

      <div class="cell">
        <h2>Account balance</h2>
        <p>Your account balance includes pending and outstanding fees.</p>
        <p>&pound;<?= (string) (\Brick\Math\BigDecimal::of((string) $balance))->withPointMovedLeft(2)->toScale(2) ?></p>
      </div>

      <div class="cell text-white bg-secondary">
        <p class="mb-0">
          <strong>
            We help keep things simple!
          </strong>
        </p>
        <p class="mb-0">
          If you switch your current account through the Current
          Account Switch Service, we'll update your details for you, before
          you even have time to tell us.
        </p>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
