<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$pagetitle = "Statement History";

$use_white_background = true;

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container-xl">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= autoUrl("payments") ?>">Payments</a></li>
				<li class="breadcrumb-item active" aria-current="page">History</li>
			</ol>
		</nav>

		<div class="">
			<h1 class="">Statement History</h1>
			<p class="lead mb-0">Previous Payments and Refunds</p>
		</div>
	</div>
</div>

<div class="container-xl">
	<?= paymentHistory(null, $user) ?>
</div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
