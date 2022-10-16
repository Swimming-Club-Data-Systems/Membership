<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Payment History";

$use_white_background = true;

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

$date = date("Y/m");

 ?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
      <li class="breadcrumb-item active" aria-current="page">History</li>
    </ol>
  </nav>

	<h1>Payment History &amp; Status</h1>
  <div class="row">
    <div class="col-md-6 col-lg-4">
      <div class="cell">
        <h2 class="border-bottom border-gray pb-2 mb-2">View Direct Debit charges by Month</h2>
        <p class="lead">Select a year and month to view the status of Direct Debit Payments.</p>
        <ul>
          <?php for ($i = 0; $i < 12; $i++) {
          $targetDate = strtotime("first day of -" . $i . " month"); ?>
          <li>
            <a href="<?=autoUrl("payments/history/" . date("Y/m", $targetDate)); ?>">
              <?=htmlspecialchars(date("F Y", $targetDate))?>
            </a>
          </li>
          <?php } ?>
        </ul>
        <h4 class="border-bottom border-gray pb-2 mb-2">View by Parent</h4>
        <p class="lead mb-0">
          <a href="<?=autoUrl("payments/history/users")?>">
            Search for a Parent
          </a>
        </p>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="cell">
        <h2 class="border-bottom border-gray pb-2 mb-2">Squad Fee Payment Status</h2>
        <p class="lead">Select to view Squad Fee Status by Month</p>
        <ul>
          <?php for ($i = 0; $i < 12; $i++) {
          $targetDate = strtotime("first day of -" . $i . " month"); ?>
          <li>
            <a href="<?=autoUrl("payments/history/squads/" . date("Y/m", $targetDate))?>">
              <?=htmlspecialchars(date("F Y", $targetDate))?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="cell">
        <h2 class="border-bottom border-gray pb-2 mb-2">Extra Fee Payment Status</h2>
        <p class="lead">Select to view Extra Fee Status by Month</p>
        <ul>
          <?php for ($i = 0; $i < 12; $i++) {
          $targetDate = strtotime("first day of -" . $i . " month"); ?>
          <li>
            <a href="<?=autoUrl("payments/history/extras/" . date("Y/m", $targetDate))?>">
              <?=htmlspecialchars(date("F Y", $targetDate))?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
