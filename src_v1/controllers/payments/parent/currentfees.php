<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Current Fees";
$use_white_background = true;

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') { ?>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
          <li class="breadcrumb-item active" aria-current="page">Latest fees</li>
        </ol>
      </nav>
    <?php } ?>

    <h1 class="">Charges since last bill</h1>
    <p class="lead mb-0">Charges and credits created since your last bill</p>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-md-8">
      <p>You'll be billed for these on the first working day of the next month.</p>
    </div>
  </div>
  <?= feesToPay(null, $user) ?>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
