<?php
http_response_code(200);
$pagetitle = "Status 200 - OK";

$currentUser = app()->user;
if ($currentUser == null) {
	include BASE_PATH . "views/head.php";
} else {
	include BASE_PATH . "views/header.php";
}
?>

<div class="container-xl">
	<div class="row">
		<div class="col-lg-8">
			<h1>The SCDS Membership System is closed for maintenance</h1>
			<p class="lead">We'll be back shortly.</p>
			<p>
				Contact our <a href="mailto:support@chesterlestreetasc.co.uk" title="Support Hotline">support address</a> if you need urgent access to database records.
			</p>
			<p>
				Gala entries and payments cannot be processed at this time - We plan scheduled downtime to avoid gala entry deadlines.
			</p>

			<p><a href="https://corporate.myswimmingclub.co.uk">myswimmingclub.co.uk</a></p>

			<hr>
			<p>HTTP Status 200 - OK.</p>

		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
