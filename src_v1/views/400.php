<?php
http_response_code(400);
$pagetitle = "Error 400 - Bad Request";
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
			<h1>We were unable to process your request</h1>
			<p class="lead">There was a problem with the request sent by your browser. This may be due to an issue with your access permissions.</p>
			<hr>
			<p>Please try the following:</p>
			<ul>
				<li>Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted correctly.</li>
				<li>If you reached this page by clicking a link, contact the Web site administrator to alert them that the link is incorrectly formatted.</li>
				<li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
			</ul>
			<p>HTTP Error 400 - Bad Request.</p>
			<hr>
			<p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.</p>

		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>
