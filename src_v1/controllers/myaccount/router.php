<?php

// My Account
$this->get('/', function () {
    halt(1);
});

$this->post('/', function () {
    halt(1);
});

// Manage Password
$this->get('/password', function () {
    halt(1);
});

$this->post('/password', function () {
    halt(1);
});

$this->get('/security-keys', function () {
    halt(1);
});

if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
	// Add swimmer
	$this->get('/add-member', function () {
		require 'add-swimmer.php';
	});

	// Add swimmer
	$this->get('/add-member/auto/{asa}/{acs}', function ($asa, $acs) {
		require 'auto-add-swimmer.php';
	});

	$this->post('/add-member', function () {
		require 'add-swimmer-action.php';
	});

	$this->get(['notify-history/', 'notifyhistory/'], function ($page = null) {
		include BASE_PATH . 'controllers/notify/MyMessageHistory.php';
	});
}

$this->get(['login-history/', 'loginhistory/'], function ($page = null) {
	halt(1);
});

$this->group('/email', function () {

	$this->group('/cc', function () {

		if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailSent']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['VerifyEmailSent'])) {
			$this->get('/new', function () {
				include 'CC/CCEmailSent.php';
			});
		} else {
			$this->post('/new', function () {
				include 'CC/NewCCVerify.php';
			});

			$this->get('/new', function () {
				header("Location: " . autoUrl("my-account/email"));
			});
		}

		$this->get('/{id}:int/delete', function ($id) {
			include 'CC/DeleteCC.php';
		});
	});

	$this->get('/', function () {
		include 'EmailOptions.php';
	});

	$this->post('/', function () {
		include 'EmailOptionsPost.php';
	});
});

$this->group(['/google-authenticator', '/googleauthenticator'], function () {
	$this->get('/', function () {
		include 'EnableGoogleAuthenticator.php';
	});

	$this->get('/setup', function () {
		include 'GoogleAuthenticatorKeyGen.php';
	});

	$this->get('/disable', function () {
		include 'GoogleAuthenticatorDisable.php';
	});

	$this->post('/setup', function () {
		include 'GoogleAuthenticatorKeyVerify.php';
	});
});

$this->group('/general', function () {
	$this->get('/', function () {
		include 'GeneralOptions.php';
	});

	$this->post('/', function () {
		include 'GeneralOptionsPost.php';
	});

	$this->get('/download-personal-data', function () {
		include 'GDPR/UserDataDump.php';
	});

	$this->get('/download-member-data/{id}:int', function ($id) {
		include 'GDPR/MemberDataDump.php';
	});
});

$this->group('/address', function () {
	$this->get('/', function () {
		include 'address.php';
	});

	$this->post('/', function () {
		include 'address-post.php';
	});
});
