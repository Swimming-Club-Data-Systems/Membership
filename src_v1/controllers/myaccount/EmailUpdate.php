<?php

$db = app()->db;
$tenant = app()->tenant;

$query = $db->prepare("SELECT * FROM `newUsers` WHERE `AuthCode` = ? AND `ID` = ? AND `Type` = ?");
$query->execute([$auth, $id, 'EmailUpdate']);

$rows = $query->fetchAll(PDO::FETCH_ASSOC);
$row = $rows[0];

$found = false;
if ($rows) {
	$found = true;
}

if ($found) {

	$array = json_decode($row['UserJSON']);

	//pre($array);

	$user 				= $array->User;
	$oldEmail 			= $array->OldEmail;
	$newEmail 			= $array->NewEmail;

	// Verify user and tenant
	$check = $db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ? AND Tenant = ?");
	$check->execute([
		$user,
		$tenant->getId(),
	]);

	if ($check->fetchColumn() == 0) {
		halt(404);
	}

	($db->prepare("UPDATE `users` SET `EmailAddress` = ? WHERE `UserID` = ?"))->execute([$newEmail, $user]);

	$subject = "Your Email Address has been Changed";
	$message = '
	<p>Your ' . app()->tenant->getKey('CLUB_NAME') . ' Account Email Address has been changed from ' . $oldEmail . ' to ' . $newEmail . '.</p>
	<p>If this was you then you, then please ignore this email. If it was not you, please head to ' . autoUrl("") . ' and reset your password urgently.</p>
	<p>Kind Regards, <br>The ' . app()->tenant->getKey('CLUB_NAME') . ' Team</p>
	';
	$to = "";
	$name = getUserName($user);
	$from = [
		"Email" => "noreply@" . getenv('EMAIL_DOMAIN'),
		"Name" => app()->tenant->getKey('CLUB_NAME') . " Secretary"
	];
	notifySend($to, $subject, $message, $name, $oldEmail, $from);

	$_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify'] = '
  <div class="alert alert-success mb-0">
    <p class="mb-0">
      <strong>
        Hello ' . $name . '! You\'ve successfully verified your new email address.
      </strong>
    </p>

    <p class="mb-0">
      We\'ve now updated it for you.
    </p>
  </div>
  ';

($db->prepare("DELETE FROM `newUsers` WHERE `AuthCode` = ? AND `ID` = ?;"))->execute([$authCode, $id]);

} else {

	$_SESSION['TENANT-' . app()->tenant->getId()]['RegistrationGoVerify'] = '
	<div class="alert alert-warning mb-0">
    <p class="mb-0">
      <strong>
        We could not find your details.
      </strong>
    </p>

    <p>
      This error may occur if your email provider has already inspected this link. Try logging in to see if this is the case.
    </p>

		<p class="mb-0">
			<a class="alert-link" href="' . autoUrl("") . '">
      	Login to your account
			</a>
    </p>
  </div>
	';
}

header("Location: " . autoUrl(""));
