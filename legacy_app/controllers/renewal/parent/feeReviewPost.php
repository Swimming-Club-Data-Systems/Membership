<?php

$db = nezamy_app()->db;

try {
	// Success move on
	$updateRenewal = $db->prepare("UPDATE `renewalProgress` SET `Substage` = `Substage` + 1 WHERE `RenewalID` = ? AND `UserID` = ?");
	$updateRenewal->execute([
		$renewal,
		$_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['UserID']
	]);
	header("Location: " . autoUrl("renewal/go"));
} catch (Exception $e) {
	$_SESSION['TENANT-' . nezamy_app()->tenant->getId()]['ErrorState'] = "
	<div class=\"alert alert-danger\">
	<p class=\"mb-0\"><strong>An error occured when we tried to update our records</strong></p>
	<p class=\"mb-0\">Please try again</p>
	</div>";
	header("Location: " . autoUrl("renewal/go"));
}
