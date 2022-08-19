<?php

$currentUser = app()->user;
$db = app()->db;
$tenant = app()->tenant;

use Respect\Validation\Validator as v;

$target = $_POST['report_url'];
$email = mb_strtolower(trim($_POST['email-address']));
$userMessage = mb_strimwidth($_POST['Message'], 0, 10000);

$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus'] = false;

if (v::url()->validate($target) && \SCDS\CSRF::verify()) {

	$message = "<p>An error has been reported on the following page: " . htmlspecialchars($target) . ".</p>";
	if ($currentUser) {
		$message .= "<p>The user was <a target=\"_blank\" href=\"" . htmlspecialchars(autoUrl('users/' . $currentUser->getId())) . "\">" . htmlspecialchars($currentUser->getFirstName() . ' ' . $currentUser->getLastName()) . "</a>, " . htmlspecialchars($currentUser->getEmail()) . ".</p>";
	} else {

		// Try and find user
		$user = null;
		try {
			$getUser = $db->prepare("SELECT UserID, Surname, Forename FROM users WHERE Active AND Tenant = ? AND EmailAddress = ?");
			$getUser->execute([
				$tenant->getId(),
				$email
			]);
			$user = $getUser->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			// Ignore
		}

		$message .= "<p>The user said their email address was " . htmlspecialchars($email) . ".";

		if ($user) {
			$message .= " There is a user, <a target=\"_blank\" href=\"" . htmlspecialchars(autoUrl('users/' . $user['UserID'])) . "\">" . htmlspecialchars($user['Forename'] . ' ' . $user['Surname']) . "</a> with this email address in the system.";
		}

		$message .= "</p>";
	}
	$message .= "<p>The user said;</p>";
	$message .= "<p>" . nl2br(htmlspecialchars($userMessage)) . "</p>";
	$message .= "<p>Reported on " . date("l j F Y") . ".</p>";
	$message .= "<p>Sent Automatically by Swimming Club Data Systems.</p>";

	notifySend("", "Website Error Report", $message, "Website Admin Team", "support@myswimmingclub.uk", ["Email" => "noreply@" . getenv('EMAIL_DOMAIN'), "Name" => 'User Error Report - SCDS']);

	$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportStatus'] = true;
	$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorReportTarget'] = $target;

	header("Location: " . autoUrl("reportanissue"));
} else if (!\SCDS\CSRF::verify()) {

	header("Location: " . autoUrl("reportanissue?url=" . urlencode($target)));

}
