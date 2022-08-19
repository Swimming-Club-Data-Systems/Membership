<?php

$db = app()->db;
$tenant = app()->tenant;

$getUser = $db->prepare("SELECT users.UserID FROM passwordTokens INNER JOIN users ON users.UserID = passwordTokens.UserID WHERE Token = ? AND users.Tenant = ? ORDER BY TokenID DESC LIMIT 1");
$getUser->execute([
	$token,
	$tenant->getId()
]);

if ($user = $getUser->fetchColumn()) {
	// Present the reset form
	include BASE_PATH . 'views/header.php';
?>
	<div class="container-xl">
		<h1>Reset Your Password</h1>
		<form method="post" class="needs-validation" novalidate>
			<div class="row">
				<div class="col-sm-6 col-md-8">

					<div class="row" id="password-row">
						<div class="mb-3 col-md-6">
							<label class="form-label" for="password-1">Password</label>
							<input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required class="form-control" name="password-1" id="password-1" aria-describedby="pwHelp">
							<small id="pwHelp" class="form-text text-muted">Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number</small>
							<div class="invalid-feedback">
								You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
							</div>
						</div>
						<div class="mb-3 col-md-6">
							<label class="form-label" for="password-2">Confirm password</label>
							<input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required class="form-control" name="password-2" id="password-2">
							<div class="invalid-feedback">
								Passwords do not match
							</div>
						</div>
					</div>

					<div class="alert alert-danger d-none" id="pwned-password-warning">
						<p class="mb-0">
							<strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Warning</strong>
						</p>
						<p class="mb-0">
							That password has been part of a data breach elsewhere on the internet. You must pick a stronger password.
						</p>
					</div>

					<p>
						<button class="btn btn-success" type="submit">
							Save password
						</button>
					</p>
				</div>
			</div>
		</form>
	</div>

	<div id="ajax-options" data-get-pwned-list-ajax-url="<?= htmlspecialchars(autoUrl('ajax-utilities/pwned-password-check')) ?>" data-cross-site-request-forgery-value="<?= htmlspecialchars(\SCDS\CSRF::getValue()) ?>"></div>

<?php
	$footer = new \SCDS\Footer();
	$footer->addJS("js/NeedsValidation.js");
	$footer->addJS("js/ajax-utilities/pwned-password-check.js");
	$footer->render();
} else {
	halt(404);
}
