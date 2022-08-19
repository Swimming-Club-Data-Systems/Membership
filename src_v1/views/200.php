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
			<h1>Status OK</h1>
			<p class="lead">This is a response to let you know the request was processed correctly. We cannot tell you more information in line with our data protection obligations.</p>
			<hr>
			<p>API Incident, Errors or Downtime? Contact the support team urgently.</p>
			<p>HTTP Status 200 - OK.</p>
			<hr>
			<p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.</p>
		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
