<?php

$pagetitle = "Renewal Completed";
include BASE_PATH . "views/header.php";
include BASE_PATH . "views/renewalTitleBar.php";
?>

<div class="container">
	<div class="">
		<h1>Thank you for renewing your membership</h1>
		<p class="lead">
			We'll charge you your renewal fee on or after the first day of next month.
		</p>

		<p>
			If you have further questions about membership renewal, please contact the
			membership officer.
		</p>

		<p>
			Your club's email address is <a href="mailto:<?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_EMAIL')) ?>"><?= htmlspecialchars(nezamy_app()->tenant->getKey('CLUB_EMAIL')) ?></a>
		</p>

		<p class="mb-0">
			<a href="<?php echo autoUrl(""); ?>" class="btn btn-success">
				Return to Dashboard
			</a>
		</p>

	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render();
