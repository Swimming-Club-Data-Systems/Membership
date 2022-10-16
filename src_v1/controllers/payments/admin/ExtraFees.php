<?php

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserId'];
$pagetitle = "Extra Fees";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

 ?>

<div class="container-xl">
	<h1>Extras</h1>
  <p class="lead">Extras include CrossFit - Fees paid in addition to Squad Fees</p>
  <div class="ajax" id="response">
    <p class="lead">Content is loading</p>
  </div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
