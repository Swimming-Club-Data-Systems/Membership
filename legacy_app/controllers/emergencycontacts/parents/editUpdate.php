<?php

$db = DB::connection()->getPdo();

$url_path = "emergency-contacts";
if ($renewal_trap) {
	$url_path = "renewal/emergencycontacts";
}

$user = $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'];

$contact = new EmergencyContact();
$contact->connect($db);
$contact->getByContactID($id);

if ($contact->getUserID() != $user) {
	halt(404);
}

if ($_POST['name'] != null && $_POST['name'] != "") {
	$contact->setName($_POST['name']);
}

if ($_POST['relation'] != null && $_POST['relation'] != "") {
	$contact->setRelation($_POST['relation']);
}

try {
	if ($_POST['num'] != null && $_POST['num'] != "") {
		$contact->setContactNumber($_POST['num']);
	}

	if ($renewal_trap) {
		header("Location: " . autoUrl("renewal/go"));
	} else {
		header("Location: " . autoUrl($url_path));
	}
	
} catch (Exception $e) {
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['PhoneError'] = true;
	header("Location: " . autoUrl($url_path . "/edit/" . $id));
}