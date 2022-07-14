<?php

// require BASE_PATH . 'controllers/payments/GoCardlessSetup.php';

$user = $id;

$name = getUserName($user);
$pagetitle = "Current Fees for " . htmlspecialchars($name);

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container-xl">
	<div class="">
		<h1 class="mb-3">
      Current Fees for <?=htmlspecialchars($name)?>
    </h1>
		<p class="lead">Fees and Charges created in the current Billing Period for <?=htmlspecialchars($name)?>.</p>
		<p>These fees will be billed on the first working day of the next month.</p>
		<?=feesToPay(null, $user)?>
	</div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
