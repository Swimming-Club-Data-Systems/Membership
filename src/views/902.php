<?php
http_response_code(503);
$pagetitle = "Error 503 - Direct Debit Disabled";
global $currentUser;
if ($currentUser == null) {
	include BASE_PATH . "views/head.php";
} else {
	include BASE_PATH . "views/header.php";
}
?>

<div class="container">
	<div class="row">
		<div class="col-lg-8">
			<h1>Your club has not enabled Direct Debit payments</h1>
			<p class="lead">Speak to your administrator for further help.</p>
			<p class="mono mb-0">Reason: No API key</p>
			<hr>
			<p>Please try the following:</p>
			<ul>
				<li>Please ensure that you are logged in with the correct account to access this resource.</li>
				<li>If you are a member of multiple clubs, please check you are logged into the right system.</li>
				<li>You may not be authorised to access this resource. Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
			</ul>
			<p>HTTP Error 503 - Service Unavailable.</p>
			<hr>
			<p class="mt-2">Contact our <a href="mailto:support@myswimmingclub.uk" title="Support Hotline">support hotline</a><?php if (!bool(env('IS_CLS'))) { ?>*<?php } ?> if the issue persists.</p>

      <?php if (!bool(env('IS_CLS'))) { ?>
      <p>* <a href="mailto:<?=htmlspecialchars(env('CLUB_EMAIL'))?>" title="<?=htmlspecialchars(env('CLUB_NAME'))?>">Contact your own club</a> in the first instance</p>
		</div>
	</div>
</div>

<?php $footer = new \SDCS\Footer();
$footer->render(); ?>
