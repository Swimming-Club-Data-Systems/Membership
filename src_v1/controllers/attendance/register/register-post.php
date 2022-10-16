<?php

use GuzzleHttp\Client;

$db = app()->db;
$tenant = app()->tenant;

try {

	// Validate user has authority
	$getMemberTenant = $db->prepare("SELECT Tenant FROM members WHERE MemberID = ?");
	$getMemberTenant->execute([
		(int) $_POST['memberId'],
	]);
	$memberTenant = $getMemberTenant->fetchColumn();

	if (!$memberTenant || $memberTenant != $tenant->getId()) {
		// No member
		reportError($_POST); // Check what was sent to check later
		throw new Exception('No such member');
	}

	$state = (int) ($_POST['state'] == 'true');
	$socketState = bool($state);
	if ($_POST['state'] == 2) {
		$state = 2;
		$socketState = null;
	}

	// Update record (if exists)
	$update = $db->prepare("UPDATE sessionsAttendance SET AttendanceBoolean = ? WHERE WeekID = ? AND SessionID = ? AND MemberID = ?");
	$update->execute([
		$state,
		(int) $_POST['weekId'],
		(int) $_POST['sessionId'],
		(int) $_POST['memberId'],
	]);

	$getCount = $db->prepare("SELECT COUNT(*) FROM sessionsAttendance WHERE WeekID = ? AND SessionID = ? AND MemberID = ?");
	$getCount->execute([
		(int) $_POST['weekId'],
		(int) $_POST['sessionId'],
		(int) $_POST['memberId'],
	]);
	$count = $getCount->fetchColumn();

	if ($count == 0) {
		throw new Exception('No attendance record');
	}

	// // Prep JSON body for socket service
	$data = [
		'room' => 'register_room:' . 'week-' . $_POST['weekId'] . '-session-' . $_POST['sessionId'],
		'field' => $_POST['memberId'],
		'state' => $socketState,
	];

	$url = 'https://production-apis.tenant-services.membership.myswimmingclub.uk/attendance/send-register-change-message';
	if (bool(getenv("IS_DEV"))) {
		$url = 'https://apis.tenant-services.membership.myswimmingclub.uk/attendance/send-register-change-message';
	}

	http_response_code(200);

	try {

		$client = new Client();

		$r = $client->request('POST', $url, [
			'json' => $data
		]);
	} catch (Exception $e) {
		// Ignore
	}
} catch (Exception $e) {

	reportError($e);

	http_response_code(500);
}
