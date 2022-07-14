<?php

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

// View a Swimmer
$this->get('/{id}:int', function ($id) {
	include 'view.php';
});

$this->get('/{id}:int/edit', function ($id) {
	include 'edit.php';
});

$this->post('/{id}:int/edit', function ($id) {
	include 'edit-post.php';
});

if ($access == "Parent") {
	// My Swimmers
	$this->get('/', function () {
		require('parentSwimmers.php');
	});


	// $leavers = app()->tenant->getKey('LeaversSquad');

	// if ($leavers != null) {
	// 	// Swimmer is leaving
	// 	$this->get('/{id}:int/leaveclub', function ($id) {
	// 		require('Leave.php');
	// 	});

	// 	// Swimmer is leaving
	// 	$this->get('/{id}:int/leaveclub/{key}', function ($id, $key) {
	// 		require('LeaveDo.php');
	// 	});
	// }

	// Edit a Swimmer
	$this->get('/{id}:int/edit', function ($id) {

		require 'parentSingleSwimmer.php';
	});

	// Edit a Swimmer
	$this->post('/{id}:int/edit', function ($id) {

		require 'parentSingleSwimmerPost.php';
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
	$this->get('/', function () {

		require('swimmerDirectory.php');
	});

	if ($access == "Admin") {
		$this->get('/orphaned', function () {

			require('swimmerOrphaned.php');
		});
	}

	$this->post('/ajax/swimmerDirectory', function () {

		include BASE_PATH . "controllers/ajax/membersList.php";
	});

	$this->get('/{swimmer}:int/enter-gala', function ($swimmer) {
		require BASE_PATH . 'controllers/galas/GalaEntryForm.php';
	});

	$this->post('/{swimmer}:int/enter-gala', function ($swimmer) {
		require BASE_PATH . 'controllers/galas/GalaEntryFormPost.php';
	});

	$this->get('/{swimmer}:int/enter-gala-success', function ($swimmer) {
		require BASE_PATH . 'controllers/galas/GalaEntryStaffSuccess.php';
	});

	/*
   * Squad moves
   *
   */
	if ($access == "Coach" || $access == 'Admin') {
		$this->get('/{id}:int/move-contract', function ($id) {
			require BASE_PATH . 'controllers/squads/SquadMoveContract.php';
		});
	}
	/*
   * End of squad moves
   */

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
	// Edit Individual Swimmers
	$this->get('/{id}:int/edit', function ($id) {

		require('singleSwimmerEdit.php');
	});

	$this->post('/{id}:int/edit', function ($id) {

		require('singleSwimmerEdit.php');
	});

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
	$this->get('/addmember', function () {
		//
		//include 'AddMember/SelectType.php';
		header("Location: " . autoUrl("members/new"));
	});

	$this->get('/new', function () {

		require('AddMember/addMember.php');
	});

	$this->post('/new', function () {

		require('AddMember/addMemberPost.php');
	});

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

$this->post('/{id}:int/squads.json', function ($id) {
	include 'moves/squads.php';
});

$this->post('/move-squad', function () {
	include 'moves/move.php';
});

$this->post('/move-operations', function () {
	include 'moves/operations.php';
});

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