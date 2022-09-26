<?php

// My Account
$this->get('/', function () {
		header("location: /my-account");
});

$this->post('/', function () {
	header("location: /my-account");
});

// Manage Password
$this->get('/password', function () {
	header("location: /my-account/password-and-security");
});

$this->post('/password', function () {
	header("location: /my-account/password-and-security");
});

$this->get('/security-keys', function () {
	header("location: /my-account/password-and-security");
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

$this->get('/email', function () {
	header("location: /my-account/email");
});

$this->group(['/google-authenticator', '/googleauthenticator'], function () {
	$this->get('/', function () {
		header("location: /my-account/password-and-security");
	});

	$this->get('/setup', function () {
		header("location: /my-account/password-and-security");
	});

	$this->get('/disable', function () {
		header("location: /my-account/password-and-security");
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
		header("location: /my-account/profile");
	});
});
