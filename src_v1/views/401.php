<?php
http_response_code(401);
$pagetitle = "Error 401 - Unauthorised";
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
			<h1>Unauthorised</h1>
			<p class="lead">Authentication has failed or has not yet been provided.</p>
			<hr>
			<p>Please try the following:</p>
			<ul>
				<li>Ensure you have logged in if required.</li>
				<li>Ensure you have logged in with an account which has access permissions for this resource.</li>
				<li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
			</ul>
			<p>HTTP Error 401 - Unauthorised.</p>
			<hr>
			<p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.</p>
		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
