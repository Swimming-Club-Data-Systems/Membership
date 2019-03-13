<?php
$userID = $_SESSION['UserID'];
$pagetitle = "Fee Review";
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/renewalTitleBar.php";
?>

<div class="container">
	<div class="mb-3 p-3 bg-white rounded shadow">
		<?php if (isset($_SESSION['ErrorState'])) {
			echo $_SESSION['ErrorState'];
			unset($_SESSION['ErrorState']);
			?><hr><?
		} ?>
		<h1>Your Fees</h1>
		<form method="post">
			<p class="lead">Here are the monthly fees you pay.</p>

			<div class="mb-3">
				<?php echo myMonthlyFeeTable($link, $userID); ?>
			</div>

			<p>You will pay these fees by Direct Debit.</p>

			<div>
				<button type="submit" class="btn btn-success">Save and Continue</button>
			</div>
		</form>
	</div>
</div>

<?php include BASE_PATH . "views/footer.php";
