<?php

use Respect\Validation\Validator as v;

$target = '';
if (isset($_GET['url'])) {
	$target = urldecode($_GET['url']);
}

$pagetitle = "Report an Issue";
include BASE_PATH . 'views/header.php'; ?>

<div class="container-xl">
	<h1>Report a Website Issue</h1>
	<?php if (app()->tenant && isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus'] == true) { ?>
		<p>We have reported that page to our team.</p>
		<p>Thank you for your feedback. It really helps us improve our website.</p>
		<p>
			<a href="<?= htmlspecialchars($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportTarget']) ?>" class="btn btn-secondary">
				Return to Page
			</a>
		</p>
		<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportTarget']); ?>
	<?php } else if (!isset($_GET['url']) || (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus']) &&
		$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus'] == false)) { ?>
		<p>We were unable to report that page. You may have not provided a URL or
			the URL was malformed.</p>
		<p>
			<a href="https://www.chesterlestreetasc.co.uk" class="btn btn-secondary">
				Return to Home
			</a>
		</p>
	<?php } else { ?>
		<p>Let us know what's wrong with the page so that we can fix it as quickly as possible.</p>
		<form method="post" class="needs-validation" novalidate>

			<?= \SCDS\CSRF::write() ?>

			<div class="mb-3">
				<label class="form-label" for="report_url">Page Address</label>
				<input type="url" value="<?= htmlspecialchars($target) ?>" readonly class="form-control" id="report_url" name="report_url">
			</div>

			<div class="mb-3">
				<label class="form-label" for="email-address">Email Address</label>
				<input type="email" <?php if (isset(app()->user)) { ?>value="<?= htmlspecialchars(app()->user->getEmail()) ?>" readonly<?php } else { ?> <?php } ?> class="form-control" id="email-address" name="email-address" required>
				<div class="invalid-feedback">
					As you're not logged in, please enter your email address so we can get in touch about the issue.
				</div>
			</div>

			<div class="mb-3">
				<label class="form-label" for="Message">What's Wrong?</label>
				<textarea class="form-control" id="Message" name="Message" rows="3" aria-describedby="MHelp"></textarea>
				<small id="MHelp" class="form-text text-muted">You don't need to fill out this box if you don't want to</small>
			</div>
			<p>
				<button class="btn btn-dark-l btn-outline-light-d" type="submit">
					Report Error
				</button>
				<a href="<?= htmlspecialchars($target) ?>" class="btn btn-danger">
					Cancel
				</a>
			</p>
		</form>
	<?php } ?>
</div>

<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus'])) {
	unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus']);
}
$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
