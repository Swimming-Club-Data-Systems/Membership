<?php

$db = app()->db;
$tenant = app()->tenant;

use Respect\Validation\Validator as v;

$getMove = $db->prepare("SELECT members.MemberID, `MForename`, `MSurname`, `SquadName`, moves.SquadID, `MovingDate` FROM ((`moves` INNER JOIN `members` ON members.MemberID = moves.MemberID) INNER JOIN `squads` ON squads.SquadID = moves.SquadID) WHERE moves.MemberID = ? AND Tenant = ?");
$getMove->execute([
	$id,
	$tenant->getId()
]);
$move = $getMove->fetch(PDO::FETCH_ASSOC);

$date = new DateTime('now', new DateTimeZone('Europe/London'));

if ($move == null) {
	halt(404);
}

$errorState = false;
$errorMessage = "";


$leavers = app()->tenant->getKey('LeaversSquad');

$newSquad = $_POST['newSquad'];
$movingDate = $_POST['movingDate'];

// Verify new squad and member
$checkMember = $db->prepare("SELECT COUNT(*) FROM members WHERE MemberID = ? AND Tenant = ?");
$checkMember->execute([
	$id,
	$tenant->getId()
]);
if ($checkMember->fetchColumn() == 0) {
	halt(404);
}

$checkSquad = $db->prepare("SELECT COUNT(*) FROM squads WHERE SquadID = ? AND Tenant = ?");
$checkSquad->execute([
	$newSquad,
	$tenant->getId()
]);
if ($checkSquad->fetchColumn() == 0) {
	halt(404);
}

$sendEmail = true;
if ($newSquad == $leavers) {
	$sendEmail = false;
}

if (!v::intVal()->validate($newSquad) || $newSquad == 0) {
	$errorState = true;
	$errorMessage .= "<li>A new squad was not supplied</li>";
}

if ($movingDate == "" || !v::date()->validate($movingDate)) {
	$errorState = true;
	$errorMessage .= "<li>A moving date was not supplied or was malformed</li>";
}

$moveDate = null;
try {
	$moveDate = new DateTime($movingDate, new DateTimeZone('Europe/London'));
	$now = new DateTime('now', new DateTimeZone('Europe/London'));
	$now->setTime(0, 0, 0);

	if ($moveDate < $now) {
		$errorState = true;
		$errorMessage .= "<li>Squad moves must be now or in the future</li>";
	}
} catch (Exception $e) {
	$errorState = true;
	$errorMessage .= "<li>Date formatting error</li>";
}

if (!$errorState) {
  try {
  	$update = $db->prepare("UPDATE `moves` SET `SquadID` = ?, `MovingDate` = ? WHERE `MemberID` = ? AND Tenant = ?");
		$update->execute([
			$newSquad,
			$movingDate,
			$id,
			$tenant->getId()
		]);
		
		if ($sendEmail) {

			// Notify the parent
			$sql = "INSERT INTO `notify` (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (?, ?, ?, ?, ?, ?)";
			$notify_query = $db->prepare($sql);

			$getParentName = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");

			$sql = "SELECT `SquadName`, `MForename`, `MSurname`, `SquadFee`, SquadCoC, `SquadTimetable`, `users`.`UserID` FROM (((`members` INNER JOIN `users` ON users.UserID = members.UserID) INNER JOIN `moves` ON members.MemberID = moves.MemberID) INNER JOIN `squads` ON moves.SquadID = squads.SquadID) WHERE members.MemberID = ? AND Tenant = ?";
			$email_info = $db->prepare($sql);
			$email_info->execute([
				$id,
				$tenant->getId()
			]);
			$email_info = $email_info->fetch(PDO::FETCH_ASSOC);

			if ($email_info) {
				$swimmer = htmlspecialchars($email_info['MForename'] . ' ' . $email_info['MSurname']);
				$parent = $email_info['UserID'];
				$squad = htmlspecialchars($email_info['SquadName']);
				$squad_fee = number_format($email_info['SquadFee'], 2, '.', ',');

				$subject = "Squad Move Update";
				$message = '<p>There has been a change to the squad move for ' . $swimmer . '. They will be moving to ' . $squad . ' Squad on ' . htmlspecialchars($moveDate->format("l j F Y")) . '.</p>';
				$message .= '<p>The Squad Fee you will pay will be &pound;' . $squad_fee . '*.</p>';
				$message .= '<p>If you pay by Direct Debit, you won\'t need to take any action. We\'ll automatically update your monthly fees.</p>';
				if ($email_info['SquadTimetable'] != "" && $email_info['SquadTimetable'] != null) {
					$message .= '<p>You can get the <a href="' . $email_info['SquadTimetable'] . '" target="_blank">timetable for ' . $squad . ' Squad on our website</a>.</p>';
				}
				if (app()->tenant->isCLS()) {
					$message .= '<p>We have attached the Code of Conduct agreement for ' . $squad . ' Squad to this email. You must print it off, sign it and return it to any squad coach or member of club staff before your first session in ' . $squad . ' Squad.</p>';
				}
				if ($email_info['SquadCoC'] != "" && $email_info['SquadCoC'] != null) {
					$message .= '<p>The terms and conditions for ' . $squad . ' Squad are as follows;</p>';
					$message .= '<div class="cell">';
					$message .= getPostContent($email_info['SquadCoC']);
					$message .= '</div>';
					$message .= '<p>You must abide by the above code of conduct if you take your place in this squad as per the Membership Terms and Conditions. This new code of conduct may be different to that for your current squad, so please read it carefully.</p>';
				}
				$message .= '<hr><p>If you do not think ' . $swimmer . ' will be able to take up their place in ' . $squad . ' Squad, please reply to this email as soon as possible. We must however warn you that we may not be able keep ' . $swimmer . ' in their current squad if it would prevent us from moving up swimmers in our lower squads.</p>';
				$message .= '<p>Kind Regards,<br>The ' . app()->tenant->getKey('CLUB_NAME') . ' Team</p>';
				$message .= '<p class="small text-muted">* Discounts may apply if you have multiple swimmers.</p>';

				try {
					$notify_query->execute([
						$parent,
						'Sent',
						$subject,
						$message,
						1,
						'SquadMove'
					]);

					$getParentName->execute([$parent]);
					$name = $getParentName->fetch(PDO::FETCH_ASSOC);

					$mailObject = new \CLSASC\SuperMailer\CreateMail();
					$mailObject->setHtmlContent($message);
					$mailObject->showName($name['Forename'] . ' ' . $name['Surname']);

					$email = new \SendGrid\Mail\Mail();
					$email->setFrom("noreply@" . getenv('EMAIL_DOMAIN'), app()->tenant->getKey('CLUB_NAME'));
					$email->setSubject($subject);
					$email->addTo($name['EmailAddress'], $name['Forename'] . ' ' . $name['Surname']);
					$email->addContent("text/plain", $mailObject->getFormattedPlain());
					$email->addContent(
						"text/html", $mailObject->getFormattedHtml()
					);

					$attachment = true;
					include BASE_PATH . 'controllers/squads/SquadMoveContract.php';
					$file_encoded = base64_encode($pdfOutput);
					$email->addAttachment(
						$file_encoded,
						"application/pdf",
						"SquadMoveContract.pdf",
						"attachment"
					);

					$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
					$sendgrid->send($email);

				} catch (Exception $e) {
					// Set error message
					$errorState = true;
					$errorMessage .= "<li>Unable to send an email to the user</li>";

					// Propagate the exception
					throw new Exception();
				}
			}
		}

		header("Location: " . autoUrl("squads/moves"));
	} catch (Exception $e) {
		$errorState = true;
		$errorMessage .= '<li>A database error occured.</li>';
	}
}


if ($errorState) {
	$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = '
	<div class="alert alert-danger">
	<strong>An error occured and we could not edit the squad move</strong>
	<ul class="mb-0">' . $errorMessage . '
	</ul></div>';

	header("Location: " . autoUrl("swimmers/" . $id . "/edit-move"));
}
