<?php
http_response_code(503);
header("Retry-After: " . date('D, d M Y H:i:s e', strtotime('+24 hours')));
header("Retry-After: 86400");
$pagetitle = "Error 503 - Service Unavailable";
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
			<h1>Service Unavailable</h1>
			<p class="lead">The service is currently unavailable because of maintenance.</p>
			<hr>
			<p>Please try again later</p>
			<p>HTTP Error 503 - Service Unavailable.</p>
			<hr>
			<p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.</p>
		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
