<?php

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
	$this->get('/', function() {
		$db = app()->db;
		
		$query = $db->prepare("SELECT COUNT(*) FROM `members` WHERE UserID = ?");
		try {
			$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
		} catch (PDOException $e) {
			halt(500);
		}
		$swimmers = $query->fetchColumn();

		$query = $db->prepare("SELECT COUNT(*) FROM `members` WHERE UserID = ? AND RR = ?");
		try {
			$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], 1]);
		} catch (PDOException $e) {
			halt(500);
		}
		$new_swimmers = $query->fetchColumn();

		if ($swimmers != $new_swimmers) {
			//include 'newmember/Welcome.php';
			include 'newfamily/Welcome.php';
		} else {
			include 'newfamily/Welcome.php';
		}
	});
} else {
	$this->get('/', function() {
		$id = 0;
		include BASE_PATH . 'controllers/renewal/admin/list.php';
	});
}

$this->group('/ac', function() {
  include 'join-from-trial/router.php';
});


$this->get('/welcome-pack', function() {
  include 'welcome-pack/PDF.php';
});
