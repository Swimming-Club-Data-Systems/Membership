<?php

$db = app()->db;
$tenant = app()->tenant;

$target = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];

$name = app()->user->getFullName();

try {
	$query = $db->prepare("SELECT * FROM `users` WHERE `UserID` = ? AND Tenant = ?");
	$query->execute([
		$_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation']['RealUser'],
		$tenant->getId()
	]);

	$info = $query->fetch(PDO::FETCH_ASSOC);

	if ($info == null) {
		halt(404);
	}

	$_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'] = null;
	$_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'] = [];
	unset($_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation']);

	$_SESSION = [];

	// session_destroy();

	$_SESSION['TENANT-' . app()->tenant->getId()]['Username'] = 		$info['Username'];
	$_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress'] = $info['EmailAddress'];
	$_SESSION['TENANT-' . app()->tenant->getId()]['Forename'] = 		$info['Forename'];
	$_SESSION['TENANT-' . app()->tenant->getId()]['Surname'] = 			$info['Surname'];
	$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] = 			$info['UserID'];
	$_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'] = 		1;

	$userObject = new \User($info['UserID'], true);
	app()->user = $userObject;

	AuditLog::new('UserSimulation-Exited', 'Stopped simulating ' . $name);

	header("Location: " . autoUrl("users/" . $target));
} catch (Exception $e) {
	reportError($e);
	header("Location: " . autoUrl(""));
}