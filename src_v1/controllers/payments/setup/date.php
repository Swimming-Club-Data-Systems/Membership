<?php

$db = app()->db;

$url_path = "payments";
if ($renewal_trap) {
	$url_path = "renewal/payments";
}

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

try {
  $getPaySchdeule = $db->prepare("SELECT * FROM `paymentSchedule` WHERE `UserID` = ?");
  $getPaySchdeule->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  $scheduleExists = $getPaySchdeule->fetch(PDO::FETCH_ASSOC);
  if ($scheduleExists != null) {
  	header("Location: " . autoUrl($url_path . "/setup/2"));
  }
} catch (Exception $e) {
  halt(500);
}

require "datepost.php";

/*

$pagetitle = "Set up a Direct Debit";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";
 ?>

<div class="container-xl">
	<h1>Setup a Direct Debit to pay Chester-le-Street ASC</h1>
	<form method="post">
		<p>On which day of the month should Chester-le-Street ASC make Direct Debit requests?</p>
		<div class="mb-3">
			<label class="form-label" for="chosenDay">Select Day</label>
			<select name="chosenDay" id="chosenDay" class="form-select">
				<?php for ($i = 1; $i < 29; $i++) {?>
			  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</div>
		<button class="btn btn-dark-l btn-outline-light-d" type="submit">Next</button>
		<p><span class="small">We'll now direct you to our partner GoCardless who handle Direct Debits on our behalf.</span></p>
	</form>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();

*/
