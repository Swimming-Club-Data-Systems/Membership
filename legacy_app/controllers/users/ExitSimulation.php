<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

$target = $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'];

$name = app()->user->getFullName();

try {
	$query = $db->prepare("SELECT * FROM `users` WHERE `UserID` = ? AND Tenant = ?");
	$query->execute([
		$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserSimulation']['RealUser'],
		$tenant->getId()
	]);

	$info = $query->fetch(PDO::FETCH_ASSOC);

	if ($info == null) {
		halt(404);
	}

	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserSimulation'] = null;
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserSimulation'] = [];
	unset($_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserSimulation']);

	$_SESSION = [];

	// session_destroy();

	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Username'] = 		$info['Username'];
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['EmailAddress'] = $info['EmailAddress'];
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Forename'] = 		$info['Forename'];
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Surname'] = 			$info['Surname'];
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'] = 			$info['UserID'];
	$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LoggedIn'] = 		1;

	$userObject = new \User($info['UserID'], true);
	app()->user = $userObject;

	AuditLog::new('UserSimulation-Exited', 'Stopped simulating ' . $name);

	header("Location: " . autoUrl("users/" . $target));
} catch (Exception $e) {
	reportError($e);
	header("Location: " . autoUrl(""));
}