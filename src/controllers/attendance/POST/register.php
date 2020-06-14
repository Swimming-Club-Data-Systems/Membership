<?php

$db = app()->db;
$tenant = app()->tenant;

$errorStatus = false;
$duplicateReg = false;
if ((isset($_POST["date"])) && (isset($_POST["squad"])) && (isset($_POST["session"]))) {
	// Happy Days. Now we just need the members

	$date = new DateTime($_POST["date"], new DateTimeZone('Europe/London'));
	$dateOriginal = clone $date;

	if ((int) $date->format('N') != '7') {
		$date->modify('last Sunday');
	}

	$weekBeginning = $date->format("Y-m-d");

	// Get the week id
	$getWeekId = $db->prepare("SELECT WeekID FROM sessionsWeek WHERE Tenant = ? AND WeekDateBeginning = ?");
	$getWeekId->execute([
		$tenant->getId(),
		$date->format("Y-m-d")
	]);
	$weekId = $getWeekId->fetchColumn();

	if ($weekId == null) {
		halt(404);
	}
	
	$squadID = $_POST["squad"];
	$sessionID = $_POST["session"];

	// Verify session is for this tenant
	$sessionCount = $db->prepare("SELECT COUNT(*) FROM `sessions` WHERE Tenant = ? AND SessionID = ?");
	$sessionCount->execute([
		$tenant->getId(),
		$sessionID
	]);

	if ($sessionCount->fetchColumn() == 0) {
		halt(404);
	}

	// SQL to check we've not done the register before
	$sql = $db->prepare("SELECT COUNT(*) FROM `sessionsAttendance` WHERE `WeekID` = ? AND `SessionID` = ?;");
	$sql->execute([
		$weekId,
		$sessionID
	]);
	$registerCount = $sql->fetchColumn();

	// SQL to get the member IDs
	$sql = $db->prepare("SELECT `MemberID` FROM `members` WHERE `SquadID` = ? AND Tenant = ?;");
	$sql->execute([
		$squadID,
		$tenant->getId()
	]);

	// Initialise the attendance values string for MySQL
	$values = "";

	$insertRecord = $db->prepare("INSERT INTO `sessionsAttendance` (WeekID, MemberID, SessionID, AttendanceBoolean) VALUES (?, ?, ?, ?);");

	// Insert the register data
	if ($registerCount < 1) {
		try {
			while ($member = $sql->fetchColumn()) {
				$attendance = 0;
				// Get the attendance value, if it is set for each member
				if (isset($_POST["Member-" . $member])) {
					$attendance = 1;
				}
				$insertRecord->execute([
					$weekId,
					$member,
					$sessionID,
					$attendance
				]);
			}

			// Return info page
			$return = "<strong>Successfully saved the session register</strong>";
			$_SESSION['TENANT-' . app()->tenant->getId()]['return'] = $return;
		} catch (Exception $e) {
			$errorStatus = true;
		}
	} else {
		try {
			$updateRecord = $db->prepare("UPDATE `sessionsAttendance` SET AttendanceBoolean = ? WHERE MemberID = ? AND SessionID = ? AND WeekID = ?;");
			while ($member = $sql->fetchColumn()) {
				$attendance = 0;
				// Get the attendance value, if it is set for each member
				if (isset($_POST["Member-" . $member])) {
					$attendance = 1;
				}
				try {
					$updateRecord->execute([
						$attendance,
						$member,
						$sessionID,
						$weekId,
					]);
				} catch (Exception $e) {
					// This catches errors with members who were not in squad when register first taken
				}
			}

			// Return info page
			$return = "<strong>Successfully saved the session register</strong>";
			$_SESSION['TENANT-' . app()->tenant->getId()]['return'] = $return;
			$duplicateReg = true;
		} catch (Exception $e) {
			$errorStatus = true;
		}
	}

	if ($duplicateReg) {
		$return = "<p class=\"mb-0\"><strong>Saved register</strong></p>";
		$_SESSION['TENANT-' . app()->tenant->getId()]['return'] = $return;
	}

} else {
	$return = "<p><strong>An Error Occurred</strong></p>";
	$_SESSION['TENANT-' . app()->tenant->getId()]['return'] = $return;
}

if ($errorStatus) {
	$return = "<p><strong>An Error Occurred</strong></p>";
	$_SESSION['TENANT-' . app()->tenant->getId()]['return'] = $return;
}

header("Location: " . autoUrl("attendance/register?date=" . urlencode($dateOriginal->format("Y-m-d")) . "&squad=" . urlencode($squadID) . "&session=" . urlencode($sessionID)));
