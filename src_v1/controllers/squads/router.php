<?php

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

if ($access == "Admin" || $access == "Coach") {
	$this->group('/moves', function () {
		require 'moves/router.php';
	});
}
