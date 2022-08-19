<?php

$fluidContainer = true;

$db = app()->db;
$currentUser = app()->user;
$tenant = app()->tenant;

$getExtraEmails = null;
try {
	$getExtraEmails = $db->prepare("SELECT ID, `Name`, EmailAddress, Verified FROM notifyAdditionalEmails WHERE UserID = ?");
	$getExtraEmails->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} catch (Exception $e) {
}

$sql = "SELECT `EmailAddress`, `EmailComms` FROM `users` WHERE `UserID` = ?";
try {
	$query = $db->prepare($sql);
	$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} catch (Exception $e) {
	halt(500);
}

$row = $query->fetch(PDO::FETCH_ASSOC);
//$current_email = $query->fetchColumn();

$emailChecked;
if ($row['EmailComms']) {
	$emailChecked = " checked ";
}

$emailChecked_security;
if (isSubscribed($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'Security')) {
	$emailChecked_security = " checked ";
}

$emailChecked_payments;
if (isSubscribed($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'Payments')) {
	$emailChecked_payments = " checked ";
}

$emailChecked_new_member;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin" && isSubscribed($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 'NewMember')) {
	$emailChecked_new_member = " checked ";
}

$getCategories = $db->prepare("SELECT `ID` `id`, `Name` `name`, `Description` `description` FROM `notifyCategories` WHERE `Tenant` = ? AND `Active` ORDER BY `Name` ASC;");
$getCategories->execute([
	$tenant->getId()
]);


$email = $row['EmailAddress'];

$pagetitle = "Email Options";
include BASE_PATH . "views/header.php";
$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
?>
<div class="container-fluid">
	<div class="row justify-content-between">
		<div class="col-md-3 d-none d-md-block">
			<?php
			$list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
			echo $list->render('email');
			?>
		</div>
		<div class="col-md-9">
			<h1>Manage Email Options</h1>
			<p class="lead">Manage your email address and email options.</p>

			<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) && $_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']) { ?>
				<div class="alert alert-success">
					<p class="mb-0">
						<strong>We've successfully updated your email options</strong>
					</p>
				</div>
			<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate']);
			} ?>

			<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateError'])) { ?>
				<div class="alert alert-success">
					<p class="mb-0">
						<?= $_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateError'] ?>
					</p>
				</div>
			<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateError']);
			} ?>

			<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdate']) && $_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdate']) { ?>
				<div class="alert alert-success">
					<p class="mb-0">
						<strong>Just one more step to update your email address</strong>
					</p>
					<p class="mb-0">
						We've sent an email to your new email address with a link in it. Please
						follow that link to confirm your new email address.
					</p>
				</div>
			<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdate']);
			} else if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdate'])) { ?>
				<div class="alert alert-danger">
					<p class="mb-0">
						<strong>The email address provided is not valid</strong>
					</p>
					<p class="mb-0">
						Please try again
					</p>
				</div>
			<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdate']);
			} ?>

			<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateNew'])) { ?>
				<div class="alert alert-info">
					<p class="mb-0">
						<strong>Once verified, your account email
							address will change to
							<?= htmlentities($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateNew']) ?></strong>
					</p>
				</div>
			<?php } ?>

			<div class="cell">
				<form method="post">
					<div class="mb-3">
						<label class="form-label" for="EmailAddress">Your email address</label>
						<input type="email" class="form-control" id="EmailAddress" name="EmailAddress" placeholder="name@example.com" value="<?= htmlentities($email) ?>">
						<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateNew'])) { ?>
							<small class="form-text">Once verified, your account email address will change to <?= htmlentities($_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateNew']) ?></small>
						<?php } ?>
					</div>

					<div class="mb-3">
						<div class="form-switch mb-2">
							<input class="form-check-input" type="checkbox" value="1" id="EmailComms" aria-describedby="EmailCommsHelp" name="EmailComms" <?= $emailChecked ?>>
							<label class="form-check-label" for="EmailComms">Receive squad updates by email</label>
							<small id="EmailCommsHelp" class="form-text text-muted">Squad updates include emails from your coaches. You'll still receive emails relating to your account if you don't receive updates.</small>
						</div>
					</div>

					<?php if ($currentUser->hasPermission('Parent')) { ?>
						<div class="mb-3">
							<div class="form-switch mb-2">
								<input class="form-check-input" type="checkbox" value="1" id="SecurityComms" aria-describedby="SecurityCommsHelp" name="SecurityComms" <?= $emailChecked_security ?>>
								<label class="form-check-label" for="SecurityComms">Receive account security emails</label>
								<small id="SecurityCommsHelp" class="form-text text-muted">Receive emails whenever somebody logs in to your account from an unrecognised <abbr title="Internet Protocol">IP</abbr> address.</small>
							</div>
						</div>

						<div class="mb-3">
							<div class="form-switch mb-2">
								<input class="form-check-input" type="checkbox" value="1" id="PaymentComms" aria-describedby="PaymentCommsHelp" name="PaymentComms" <?php echo $emailChecked_payments; ?>>
								<label class="form-check-label" for="PaymentComms">Receive payment emails</label>
								<small id="PaymentCommsHelp" class="form-text text-muted">If you opt out, you'll still receive emails required for regulatory purposes.</small>
							</div>
						</div>
					<?php } ?>

					<?php if ($currentUser->hasPermission('Admin')) { ?>
						<div class="mb-3">
							<div class="form-switch mb-2">
								<input class="form-check-input" type="checkbox" value="1" id="NewMemberComms" aria-describedby="NewMemberCommsHelp" name="NewMemberComms" <?php echo $emailChecked_new_member; ?>>
								<label class="form-check-label" for="NewMemberComms">Receive new member emails</label>
								<small id="NewMemberCommsHelp" class="form-text text-muted">Get notified when new members are added.</small>
							</div>
						</div>
					<?php } ?>

					<?php while ($category = $getCategories->fetch(PDO::FETCH_OBJ)) { ?>
						<div class="mb-3">
							<div class="form-switch mb-2">
								<input class="form-check-input" type="checkbox" value="1" id="<?= htmlspecialchars('email-category-' . $category->id) ?>" aria-describedby="<?= htmlspecialchars('help-email-category-' . $category->id) ?>" name="<?= htmlspecialchars('email-category-' . $category->id) ?>" <?php if (isAbsolutelySubscribed(app()->user->getId(), $category->id)) { ?>checked<?php } ?>>
								<label class="form-check-label" for="<?= htmlspecialchars('email-category-' . $category->id) ?>"><?= htmlspecialchars($category->name) ?></label>
								<small id="<?= htmlspecialchars('help-email-category-' . $category->id) ?>" class="form-text text-muted"><?= htmlspecialchars($category->description) ?></small>
							</div>
						</div>
					<?php } ?>

					<p class="mb-0">
						<button type="submit" class="btn btn-success">Save Changes</button>
					</p>
				</form>
			</div>

			<?php if ($currentUser->hasPermission('Parent')) { ?>

				<div class="cell">
					<h2>Additional Recipients</h2>
					<p class="lead">
						You can have squad update emails sent to additional email addresses.
					</p>
					<p>
						This allows your partner or others involved with your swimmers to also be able to stay up to date.
					</p>
					<p>
						Additional recipients will be required to confirm they would like to receive emails. They will be able to unsubscribe at any time.
					</p>

					<ul class="list-unstyled">
						<?php while ($extraEmails = $getExtraEmails->fetch(PDO::FETCH_ASSOC)) { ?>
							<li class="mb-2">
								<p class="text-truncate mb-0">
									<strong><?= htmlspecialchars($extraEmails['EmailAddress']) ?></strong> <?php if (!bool($extraEmails['Verified'])) { ?><i title="Email awaiting verification" class="text-warning fa fa-times-circle fa-fw" aria-hidden="true"></i><?php } else { ?><i title="Email address verified" class="text-success fa fa-check-circle fa-fw" aria-hidden="true"></i><?php } ?>
								</p>
								<p>
									<a href="<?= autoUrl("my-account/email/cc/" . $extraEmails['ID'] . "/delete") ?>">
										Delete this email
									</a>
								</p>
							</li>
						<?php } ?>
					</ul>

					<form id="cc" method="post" action="<?= autoUrl("my-account/email/cc/new") ?>" class="needs-validation" novalidate>

						<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailError']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailError'])) { ?>
							<div class="alert alert-warning">
								<p class="mb-0"><strong>There was a problem with the information you supplied.</strong></p>
								<p class="mb-0">Please try again.</p>
							</div>
						<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailError']);
						} ?>

						<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['DeleteCCSuccess'])) {
							unset($_SESSION['TENANT-' . app()->tenant->getId()]['DeleteCCSuccess']); ?>
							<div class="alert alert-success">
								<p class="mb-0">
									<strong>We've deleted that additional email</strong>
								</p>
							</div>
						<?php } ?>

						<div class="row">
							<div class="col-md">
								<div class="mb-3">
									<label class="form-label" for="new-cc-name">Name</label>
									<input type="text" class="form-control" id="new-cc-name" name="new-cc-name" placeholder="Joe Bloggs" required>
								</div>
							</div>
							<div class="col-md">
								<div class="mb-3">
									<label class="form-label" for="new-cc">CC Email Address</label>
									<input type="email" class="form-control" id="new-cc" name="new-cc" placeholder="joe.bloggs@example.com" required>
								</div>
							</div>
						</div>

						<button class="btn btn-success" type="submit">
							Add new CC Email
						</button>
					</form>

				</div>

			<?php } ?>

		</div>
	</div>
</div>

<?php $footer = new \SCDS\Footer();
$footer->addJS("js/NeedsValidation.js");
$footer->useFluidContainer();
$footer->render(); ?>