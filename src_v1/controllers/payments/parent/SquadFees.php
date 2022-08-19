<?php

$pagetitle = 'My Squad and Extra Fees';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">
    <div class="row">
      <div class="col-lg-8">
        <h1>My monthly fees</h1>
        <p class="lead mb-0">These are the fees you pay each month.</p>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row">
    <div class="col-lg-8">
      <p>In some months, additional charges may be added to your account to cover the cost of gala entries and club membership.</p>

      <div class="mb-3">
        <?= myMonthlyFeeTable(null, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) ?>
      </div>

      <p>Contact your club if you have any questions about these fees.</p>

    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
