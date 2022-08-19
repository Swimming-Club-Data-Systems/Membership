<?php
http_response_code(403);
$pagetitle = "Error 403 - Forbidden";
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
			<h1>You are not allowed access to the page you requested</h1>
			<p class="lead">Please ensure that you are logged in with the correct account to access this resource.</p>
			<p class="font-monospace mb-0">Reason: Regulatory</p>
			<hr>
			<p>Please try the following:</p>
			<ul>
				<li>Please ensure that you are logged in with the correct account to access this resource.</li>
				<li>You may not be authorised to access this resource. Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
			</ul>
			<p>HTTP Error 403 - Forbidden.</p>
			<hr>
			<p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.</p>
		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
