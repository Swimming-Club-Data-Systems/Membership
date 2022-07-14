<?php

$db = app()->db;

$url_path = "emergencycontacts";
if ($renewal_trap) {
	$url_path = "renewal/emergencycontacts";
}

$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$contact = new EmergencyContact();
$contact->connect($db);
$contact->getByContactID($id);

if ($contact->getUserID() != $user) {
	halt(404);
}

$contact->delete();

header("Location: " . autoUrl($url_path));
