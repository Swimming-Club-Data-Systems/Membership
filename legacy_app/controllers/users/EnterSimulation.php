<?php

$db = DB::connection()->getPdo();
$tenant = tenant()->getLegacyTenant();

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

$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserSimulation'] = [
	'RealUser'    => $_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'],
	'SimUser'     => $info['UserID'],
	'SimUserName' => \SCDS\Formatting\Names::format($info['Forename'], $info['Surname'])
];

$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Username'] =     $info['Username'];
$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['EmailAddress'] = $info['EmailAddress'];
$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Forename'] =     $info['Forename'];
$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['Surname'] =      $info['Surname'];
$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['UserID'] =       $info['UserID'];
$_SESSION['TENANT-' . tenant()->getLegacyTenant()->getId()]['LoggedIn'] =     1;

$userObject = new \User($id, true);

AuditLog::new('UserSimulation-Entered', 'Started simulating ' . $userObject->getFullName());

header("Location: " . autoUrl(""));
