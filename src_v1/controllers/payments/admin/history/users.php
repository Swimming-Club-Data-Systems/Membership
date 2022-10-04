<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$name = getUserName($id);

if (!$name) {
	halt(404);
}

$use_white_background = true;
$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = htmlspecialchars($name) . "'s Transaction History";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">
	<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
			<li class="breadcrumb-item"><a href="<?=autoUrl("payments/history")?>">History &amp; Status</a></li>
			<li class="breadcrumb-item"><a href="<?=autoUrl("payments/history/users")?>">Find a parent</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?=htmlspecialchars($name)?></li>
    </ol>
  </nav>
	<div class="">
		<h1 class="border-bottom border-gray pb-2 mb-2">
			Transaction History for <?=htmlspecialchars($name)?>
		</h1>
		<p class="lead">Previous Payments and Refunds</p>
		<?=paymentHistory(null, $id, "admin")?>
	</div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
