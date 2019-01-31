<?php

$use_white_background = true;

global $db;

$sql = "SELECT `EmailAddress`, `EmailComms` FROM `users` WHERE `UserID` = ?";
try {
	$query = $db->prepare($sql);
	$query->execute([$_SESSION['UserID']]);
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
if (isSubscribed($_SESSION['UserID'], 'Security')) {
	$emailChecked_security = " checked ";
}

$emailChecked_payments;
if (isSubscribed($_SESSION['UserID'], 'Payments')) {
	$emailChecked_payments = " checked ";
}

$emailChecked_new_member;
if ($_SESSION['AccessLevel'] == "Admin" && isSubscribed($_SESSION['UserID'], 'NewMember')) {
	$emailChecked_new_member = " checked ";
}


$email = $row['EmailAddress'];

$pagetitle = "Email Options";
include BASE_PATH . "views/header.php";
  $userID = $_SESSION['UserID'];
?>
<div class="container">
  <h1>Manage Email Options</h1>
  <p class="lead">Manage your email address and email options.</p>

	<? if ($_SESSION['OptionsUpdate']) { ?>
		<div class="alert alert-success">
			<p class="mb-0">
				<strong>We've successfully updated your email options</strong>
			</p>
		</div>
	<? unset($_SESSION['OptionsUpdate']);
	} ?>

	<? if ($_SESSION['EmailUpdate']) { ?>
		<div class="alert alert-success">
			<p class="mb-0">
				<strong>Just one more step to update your email address</strong>
			</p>
			<p class="mb-0">
				We've sent an email to your new email address with a link in it. Please
				follow that link to confirm your new email address.
			</p>
		</div>
	<? unset($_SESSION['EmailUpdate']);
	} else if (isset($_SESSION['EmailUpdate'])) { ?>
		<div class="alert alert-danger">
			<p class="mb-0">
				<strong>The email address provided is not valid</strong>
			</p>
			<p class="mb-0">
				Please try again
			</p>
		</div>
		<? unset($_SESSION['EmailUpdate']);
	} ?>

	<? if (isset($_SESSION['EmailUpdateNew'])) { ?>
		<div class="alert alert-info">
			<p class="mb-0">
				<strong>Once verified, your account email
				address will change to
				<?=htmlentities($_SESSION['EmailUpdateNew'])?></strong>
			</p>
			<p class="mb-0">
				If you need help, contact <a href="mailto:support@chesterlestreetasc.co.uk" class="alert-link">support@chesterlestreetasc.co.uk</a>
			</p>
		</div>
	<? } ?>

	<div class="cell">
		<form method="post">
			<div class="form-group">
		    <label for="EmailAddress">Your Email address</label>
		    <input type="email" class="form-control" id="EmailAddress" name="EmailAddress" placeholder="name@example.com" value="<?=htmlentities($email)?>">
				<? if (isset($_SESSION['EmailUpdateNew'])) { ?>
				<small class="form-text">Once verified, your account email
				address will change to
				<?=htmlentities($_SESSION['EmailUpdateNew'])?></small>
				<? } ?>
		  </div>

			<div class="form-group">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" value="1" id="EmailComms" aria-describedby="EmailCommsHelp" name="EmailComms" <?php echo $emailChecked; ?> >
          <label class="custom-control-label" for="EmailComms">Receive Squad Updates by Email</label>
					<small id="EmailCommsHelp" class="form-text text-muted">You'll still receive emails relating to your account if you don't receive news</small>
				</div>
			</div>

			<div class="form-group">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" value="1" id="SecurityComms" aria-describedby="SecurityCommsHelp" name="SecurityComms" <?php echo $emailChecked_security; ?> >
          <label class="custom-control-label" for="SecurityComms">Receive Account Security Emails</label>
					<small id="SecurityCommsHelp" class="form-text text-muted">Receive emails whenever somebody logs in to your account</small>
				</div>
			</div>

			<div class="form-group">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" value="1" id="PaymentComms" aria-describedby="PaymentCommsHelp" name="PaymentComms" <?php echo $emailChecked_payments; ?> >
          <label class="custom-control-label" for="PaymentComms">Receive Payment Emails</label>
					<small id="PaymentCommsHelp" class="form-text text-muted">If you opt out, you'll still receive emails required for regulatory purposes</small>
				</div>
			</div>

			<? if ($_SESSION['AccessLevel'] == "Admin") { ?>
			<div class="form-group">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" value="1" id="NewMemberComms" aria-describedby="NewMemberCommsHelp" name="NewMemberComms" <?php echo $emailChecked_new_member; ?> >
          <label class="custom-control-label" for="NewMemberComms">Receive New Member Emails</label>
					<small id="NewMemberCommsHelp" class="form-text text-muted">Get notified when new members are added</small>
				</div>
			</div>
			<? } ?>

			<p class="mb-0">
				<button type="submit" class="btn btn-secondary">Update Details</button>
			</p>
		</form>
	</div>
</div>

<?php include BASE_PATH . "views/footer.php"; ?>
