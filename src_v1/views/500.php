<?php

$customBackground = "error-warning-background";

global $e;
$reportedError = false;
if (!bool(getenv('IS_DEV')) ||  app('request')->method != 'GET') {
	$reportedError = reportError($e);
}

http_response_code(500);
$pagetitle = "Error 500 - Internal Server Error";
$currentUser = app()->user;
if ($currentUser == null) {
	include BASE_PATH . "views/head.php";
} else {
	include BASE_PATH . "views/header.php";
}
?>

<main>
	<div class="container-xl">
		<div class="row">
			<div class="col-lg-8">
				<h1>Internal Server Error</h1>
				<p class="lead <?php if (!$reportedError) { ?> mb-0 <?php } ?>">Something went wrong so we are unable to serve you this page. We're sorry that this has occured.</p>
			</div>

			<?php if (bool(getenv('IS_DEV')) && isset($e)) { ?>
				<div class="col-12">
					<?php pre($e); ?>
				</div>
			<?php } ?>

			<div class="col-lg-8">
				<?php if ($reportedError) { ?>
					<p>
						<strong>
							Full details of this error have been reported automatically to your system administrator.
						</strong>
					</p>
				<?php } ?>
				<hr>
				<p>Please try the following:</p>
				<ul>
					<li>If this is the first time you have seen this error, try reloading the page.</li>
					<li>If you are asked to confirm form resubmission, say <strong>no</strong> and press <a href="javascript:history.back(1)">Back</a> instead.</li>
					<li>If you keep seeing this error, please try again later.</li>
				</ul>
				<p>HTTP Error 500 - Internal Server Error.</p>
				<hr>

				<p class="mt-2"><a href="mailto:support@myswimmingclub.uk" title="Support Hotline">Email SCDS</a> for help and support if the issue persists.</p>
			</div>
		</div>
	</div>
</main>

<?php $footer = new \SCDS\Footer();
$footer->render(); ?>