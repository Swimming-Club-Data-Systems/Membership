<?php

if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status']) || $_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] == null) {
  halt(404);
}

$pagetitle = "You've setup a Direct Debit";
if ($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] != 'success') {
  $pagetitle = 'An error has occurred';
} else {
  $_SESSION['TENANT-' . app()->tenant->getId()]['RegRenewalDDSuccess'] = true;
}

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] == 'success' || $_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] == 'redirect_flow_already_completed') { ?>

      <h1>You've successfully set up your new direct debit.</h1>
      <p class="lead">GoCardless will appear on your bank statement when
        payments are taken against this Direct Debit.</p>
      <p>GoCardless handles direct debit payments for <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>.</p>
      <?php if (isset($renewal_trap) && $renewal_trap) { ?>
      <a href="<?php echo autoUrl("renewal/go"); ?>" class="mb-3 btn btn-success">Continue registration or renewal</a>
      <?php } else { ?>
      <a href="<?php echo autoUrl("payments"); ?>" class="mb-3 btn btn-dark-l btn-outline-light-d">Go to Payments</a>
      <?php } ?>

      <?php } else if ($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] == 'redirect_flow_incomplete') { ?>

      <h1>Form not completed</h1>
      <p class="lead">You have not completed the required form to set up a direct debit mandate.</p>
      <p><a href="<?=autoUrl($url_path . "/setup/2")?>">Complete form again</a></p>

      <?php } else { ?>

      <h1>An unexpected error has occurred</h1>
      <p class="lead">Please try again.</p>
      <p><a href="<?=autoUrl($url_path . "/setup/2")?>">Complete form again</a></p>

      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();

$_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status'] = null;
unset($_SESSION['TENANT-' . app()->tenant->getId()]['GC-Setup-Status']);