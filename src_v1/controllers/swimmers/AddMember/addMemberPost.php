<?php

if (!SCDS\CSRF::verify()) {
	halt(403);
}

$db = app()->db;
$tenant = app()->tenant;

$squads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$squads->execute([
	$tenant->getId(),
]);

$added = $action = false;

$forename = $middlenames = $surname = $dateOfBirth = $asaNumber = $sex = $cat = $cp = $sep = $sql = $transfer = "";
$getASA = false;

if ((!empty($_POST['forename'])) && (!empty($_POST['surname'])) && (!empty($_POST['datebirth'])) && (!empty($_POST['sex']))) {
	$forename = trim(ucwords($_POST['forename']));
	$surname = trim(ucwords($_POST['surname']));
	$dateOfBirth = trim($_POST['datebirth']);
	$sex = $_POST['sex'];
	if ((!empty($_POST['middlenames']))) {
		$middlenames = trim(ucwords($_POST['middlenames']));
	}
	if ((!empty($_POST['asa']))) {
		$asaNumber = trim($_POST['asa']);
	} else {
		$getASA = true;
	}
	if ($asaNumber == "" || $asaNumber == null) {
		$getASA = true;
	}

	if (isset($_POST['clubpays']) && bool($_POST['clubpays'])) {
		$sep = 1;
	} else {
		$sep = 0;
	}

	if (isset($_POST['clubmemb']) && bool($_POST['clubmemb'])) {
		$cp = 1;
	} else {
		$cp = 0;
	}

	$transfer = 0;

	$getClass = $db->prepare("SELECT `ID`, `Name`, `Description`, `Fees` FROM `clubMembershipClasses` WHERE `ID` = ? AND `Tenant` = ? AND `Type` = ?");

	$membershipClass = null;
	if (isset($_POST['membership-class'])) {
		$getClass->execute([
			$_POST['membership-class'],
			$tenant->getId(),
			'club'
		]);
		$class = $getClass->fetch(PDO::FETCH_ASSOC);

		if (!$class) {
			throw new Exception('Membership class not found at this tenant');
		}
		$membershipClass = $_POST['membership-class'];
	}

	$checkCat = $db->prepare("SELECT COUNT(*) FROM `clubMembershipClasses` WHERE `ID` = ? AND `Tenant` = ? AND `Type` = ?");
	$ngbCat = null;

	if ($_POST['ngb-cat'] != 'none') {
		$checkCat->execute([
			$_POST['ngb-cat'],
			$tenant->getId(),
			'national_governing_body'
		]);

		if ($checkCat->fetchColumn() == 0) {
			throw new Exception('Invalid national governing body membership category');
		}

		$ngbCat = $_POST['ngb-cat'];
	}

	$accessKey = generateRandomString(6);

	try {
		$insert = $db->prepare("INSERT INTO `members` (MForename, MMiddleNames, MSurname, DateOfBirth, ASANumber, Gender, AccessKey, NGBCategory, ClubPaid, ASAPaid, OtherNotes, RRTransfer, Tenant, ClubCategory) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$insert->execute([
			$forename,
			$middlenames,
			$surname,
			$dateOfBirth,
			$asaNumber,
			$sex,
			$accessKey,
			$ngbCat,
			$cp,
			$sep,
			"",
			$transfer,
			$tenant->getId(),
			$membershipClass,
		]);

		$last_id = $db->lastInsertId();

		// If squad, add to squad

		if ($getASA) {
			$swimEnglandTemp = app()->tenant->getKey('ASA_CLUB_CODE') . $last_id;
			$addTempSwimEnglandCode = $db->prepare("UPDATE `members` SET `ASANumber` = ? WHERE `MemberID` = ?");
			$addTempSwimEnglandCode->execute([$swimEnglandTemp, $last_id]);
		}

		AuditLog::new('Members-Added', 'Added ' . $forename . ' ' . $surname . ' (#' . $last_id . ')');

		$addToSquad = $db->prepare("INSERT INTO squadMembers (Member, Squad, Paying) VALUES (?, ?, ?)");

		while ($squad = $squads->fetch(PDO::FETCH_ASSOC)) {
			try {
				if (isset($_POST['squad-' . $squad['SquadID']]) && bool($_POST['squad-' . $squad['SquadID']])) {
					// Add member to the squad
					$addToSquad->execute([
						$last_id,
						$squad['SquadID'],
						(int) true,
					]);
				}
			} catch (PDOException $e) {
				// Catch any already exists errors, despite fact this is not expected
			}
		}

		$action = true;

		try {
			$getAdmins = $db->prepare("SELECT `UserID` FROM `users` INNER JOIN `permissions` ON users.UserID = `permissions`.`User` WHERE Tenant = ? AND `Permission` = 'Admin' AND `UserID` != ?");
			$getAdmins->execute([
				$tenant->getId(),
				$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
			]);
			$notify = $db->prepare("INSERT INTO notify (`UserID`, `Status`, `Subject`, `Message`, `ForceSend`, `EmailType`) VALUES (?, 'Queued', ?, ?, 0, 'NewMember')");
			$subject = "New Club Member";
			$message = '<p>' . htmlentities(getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) . ' has added a new member, <a href="' . htmlspecialchars(autoUrl('members/' . $last_id)) . '" target="_blank">' . htmlentities($forename . ' ' . $surname) . '</a> to our online membership system.</p><p>We have sent you this email (because you\'re an admin) to ensure you\'re aware of this.</p>';
			while ($row = $getAdmins->fetch(PDO::FETCH_ASSOC)) {
				try {
					$notify->execute([$row['UserID'], $subject, $message]);
				} catch (PDOException $e) {
					//halt(500);
				}
			}
		} catch (PDOException $e) {
		}
	} catch (Exception $e) {
		reportError($e);
		$action = false;
	}
}

if ($action) {
	$_SESSION['TENANT-' . app()->tenant->getId()]['SwimmerAdded'] = true;
	header("Location: " . autoUrl("members/" . $last_id));
} else {
	$_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] = '
	<div class="alert alert-danger">
		<p class="mb-0">
			<strong>We were not able to add the new swimmer</strong>
			Please try again
		</p>
	</div>';
	header("Location: " . autoUrl("members/new"));
}
