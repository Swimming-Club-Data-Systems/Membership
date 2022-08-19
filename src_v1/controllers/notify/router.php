<?php

$db = app()->db;

$this->get('/lambda', function () {

	// $client = new GuzzleHttp\Client();
	// $response = $client->post('/2015-03-31/functions/arn:aws:lambda:eu-west-2:684636513987:function:Email-Queue/invocations', [
	// 	'headers' => [
	// 		'X-Amz-Invocation-Type' => 'Event',
	// 	]
	// ]);

	$sharedConfig = [
		'region' => 'eu-west-2',
		'version' => 'latest'
	];

	// Create an SDK class used to share configuration across clients.
	$sdk = new Aws\Sdk($sharedConfig);

	$client = $sdk->createLambda();

	// pre($client);
});

$rep = false;
$repBlocked = false;
if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
	$getSquadCount = $db->prepare("SELECT COUNT(*) FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad AND squadReps.User = ?");
	$getSquadCount->execute([
		$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
	]);
	if ($getSquadCount->fetchColumn() > 0) {
		$rep = true;
	}

	// If rep blocked, block access to notify
	if (app()->tenant && app()->tenant->getBooleanKey('BLOCK_SQUAD_REPS_FROM_NOTIFY')) {
		$repBlocked = true;
	}

	$getListCount = $db->prepare("SELECT COUNT(*) FROM `targetedLists` INNER JOIN listSenders ON listSenders.List = targetedLists.ID WHERE listSenders.User = ?");
	$getListCount->execute([
		$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
	]);
	if ($getListCount->fetchColumn() > 0) {
		$rep = true;
		$repBlocked = false;
	}
}

define('REP_BLOCKED', $repBlocked);

$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

$this->post('/save-user-settings', function () {
	include 'save-user-settings.php';
});

// if ($access != "Admin" && $access != "Coach" && $access != "Galas" && !$rep) {
// 	$this->get('/', function () {

// 		include 'Help.php';
// 	});
// }

if ($access == "Admin" || $access == "Coach" || $access == "Galas" || $rep) {
	// $this->get('/', function () {
	// 	include 'Home.php';
	// });

	$this->post('/file-uploads', function () {
		include 'FileUploads.php';
	});

	$this->post('/send-email', function () {
		$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
		if (!($access == "Admin" || $access == "Coach" || $access == "Galas") && REP_BLOCKED) {
			halt(404);
		} else {
			include 'send-email.php';
		}
	});

	$this->group(['/new', '/newemail'], function () {

		$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
		if (!($access == "Admin" || $access == "Coach" || $access == "Galas") && REP_BLOCKED) {
			$this->any(['/', '/*'], function () {
				include 'RepBlocked.php';
			});
		}

		/*
  	$this->get('/', function() {
  		include 'Email.php';
  	});
		*/

		$this->get('/react-data', function () {
			include 'EmailAPIData.php';
		});

		$this->post('/image-upload', function () {
			include 'ImageUpload.php';
		});

		/*
  	$this->post('/', function() {
  		include 'EmailQueuer.php';
  	});
		*/

		$this->get('/individual/{user}?:int/', function ($user = null) {

			include 'EmailIndividual.php';
		});

		$this->post('/individual/{user}?:int/', function ($user = null) {

			include 'EmailQueuerIndividual.php';
		});
	});

	$this->get('/reply-to', function () {
		include 'ReplyTo.php';
	});

	$this->post('/reply-to', function () {
		include 'ReplyToPost.php';
	});

	if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") {
		$this->get('/pending', function () {

			include 'EmailList.php';
		});

		$this->get('/email/{id}:int', function ($id) {

			include 'EmailID.php';
		});
	}

	$this->group('/history', function () {
		$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];
		if (!($access == "Admin" || $access == "Coach" || $access == "Galas") && REP_BLOCKED) {
			$this->any(['/', '/*'], function () {
				include 'RepBlocked.php';
			});
		}

		$this->get('/', function () {
			include 'MessageHistory.php';
		});
	});

	if (!$rep) {

		$this->group('/lists', function () {


			$this->get('/', function () {

				include 'ListOfLists.php';
			});

			$this->get('/new', function () {

				include 'NewList.php';
			});

			$this->post('/new', function () {

				include 'NewListServer.php';
			});

			$this->get('/{id}:int', function ($id) {

				include 'ListIndividual.php';
			});

			$this->post('ajax/{id}:int', function ($id) {

				include 'ListIndividualServer.php';
			});

			$this->get('/{id}:int/edit', function ($id) {
				include 'EditList.php';
			});

			$this->post('/{id}:int/edit', function ($id) {

				include 'EditListServer.php';
			});

			$this->get('/{id}:int/delete', function ($id) {

				include 'DeleteList.php';
			});
		});
	}

	if (app()->user->hasPermissions(['Admin', 'Coach'])) {
		$this->get('/sms', function () {
			$db = app()->db;
			include 'SMSList.php';
		});

		$this->post('/sms/ajax', function () {
			$db = app()->db;
			include 'SMSListFetch.php';
		});
	}
}
