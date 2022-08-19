<?php

$db = app()->db;
$tenant = app()->tenant;

try {
	$query = $db->prepare("SELECT * FROM `users` WHERE Tenant = ? AND `UserID` = ? AND Active");
	$query->execute([
		$tenant->getId(),
		$id
	]);
} catch (Exception $e) {
	halt(500);
}

$info = $query->fetch(PDO::FETCH_ASSOC);

if (!$info) {
	halt(404);
}

$_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'] = [
	'RealUser'    => $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
	'SimUser'     => $info['UserID'],
	'SimUserName' => \SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])
];

$_SESSION['TENANT-' . app()->tenant->getId()]['Username'] =     $info['Username'];
$_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'] = $info['EmailAddress'];
$_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] =     $info['Forename'];
$_SESSION['TENANT-' . app()->tenant->getId()]['Surname'] =      $info['Surname'];
$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] =       $info['UserID'];
$_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'] =     1;

$userObject = new \User($id, true);

AuditLog::new('UserSimulation-Entered', 'Started simulating ' . $userObject->getFullName());

header("Location: " . autoUrl(""));
