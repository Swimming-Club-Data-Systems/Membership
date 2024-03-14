<?php

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

if ($access == "Parent") {
	// My Swimmers
	$this->get('/', function () {
		header("location: /#members");
	});

	$this->group('/{id}:int/password', function ($id) {
		$this->get('/', function ($id) {
			include 'member-accounts/password.php';
		});

		$this->post('/', function ($id) {
			include 'member-accounts/password-post.php';
		});
	});
} else if ($access == "Committee" || $access == "Galas" || $access == "Coach" || $access == "Admin") {
	// Directory
	if ($access == "Admin") {
		$this->get('/orphaned', function () {
			require('swimmerOrphaned.php');
		});
	}

	$this->get('/{swimmer}:int/enter-gala', function ($swimmer) {
		require BASE_PATH . 'controllers/galas/GalaEntryForm.php';
	});

	$this->post('/{swimmer}:int/enter-gala', function ($swimmer) {
		require BASE_PATH . 'controllers/galas/GalaEntryFormPost.php';
	});

	$this->get('/{swimmer}:int/enter-gala-success', function ($swimmer) {
		require BASE_PATH . 'controllers/galas/GalaEntryStaffSuccess.php';
	});

	/**
	 * Member access passwords
	 */

	$this->group('/{id}:int/password', function ($id) {
		$this->get('/', function ($id) {
			include 'member-accounts/password.php';
		});

		$this->post('/', function ($id) {
			include 'member-accounts/password-post.php';
		});
	});

	// /*
	$this->get('/{id}:int/contact-parent', function ($id) {
		$user = getSwimmerParent($id);
		$swimmer = $id;
		include BASE_PATH . 'controllers/notify/EmailIndividual.php';
	});

	$this->post('/{id}:int/contact-parent', function ($id) {
		$user = getSwimmerParent($id);
		$returnToSwimmer = true;
		$swimmer = $id;
		include BASE_PATH . 'controllers/notify/EmailQueuerIndividual.php';
	});
	// */

	if ($access != "Galas") {
		$this->get('/{id}:int/attendance', function ($id) {
			include BASE_PATH . "controllers/attendance/historyViews/swimmerHistory.php";
		});

		// Access Keys
		$this->get('/access-keys', function () {
			require('accesskeys.php');
		});

		// Access Keys
		$this->get('/access-keys.csv', function () {
			require('accesskeysCSV.php');
		});
	}
}

if ($access == "Admin") {
	$this->group('/reports', function () {
		$this->get('/upgradeable', function () {
			include "reports/UpgradeableMembers.php";
		});

		$this->post('/upgradeable', function () {
			include "reports/UpgradeableMembersPost.php";
		});
	});
}

/**
 * Manage times for swimmers
 */
$this->get('/{id}:int/edit-times', function ($id) {
	require 'times/times.php';
});

$this->post('/{id}:int/edit-times', function ($id) {
	require 'times/times-post.php';
});

if ($access != "Parent" && $access != 'Galas') {
	$this->get(['/{id}:int/parenthelp', '/parenthelp/{id}:int'], function ($id) {
		include 'parentSetupHelp.php';
	});
}

// View Medical Notes
$this->get('/{id}:int/medical', function ($id) {
	include 'medicalDetails.php';
});

// View Medical Notes
$this->post('/{id}:int/medical', function ($id) {
	include 'medicalDetailsPost.php';
});

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent") {
	$this->get('/{swimmer}:int/agreement-to-code-of-conduct/{squad}:int', function ($swimmer, $squad) {
		include 'MarkCodeOfConductCompleted.php';
	});
}

$this->group('/{swimmer}:int/times', function () {
	include 'times/router.php';
});

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Admin') {
	$this->post('/delete', function () {
		include 'delete.php';
	});
}

$this->group('/{id}:int/qualifications', function($id) {
	$this->get('/', function($id) {
		include 'qualifications/list.php';
	});

	$this->get('/current', function($id) {
		include 'qualifications/current.php';
	});

	$this->get('/new', function($id) {
		include 'qualifications/new.php';
	});

	$this->post('/new', function($id) {
		include 'qualifications/new-post.php';
	});
});