<?php

$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$access = $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'];

$this->get('/', function () {
	require 'squadList.php';
});

$this->get('/{id}:int', function ($id) {
	require 'squad.php';
});

if ($access == "Committee" || $access == "Admin" || $access == "Coach") {

	$this->get('/', function () {

		require 'squadList.php';
	});

	$this->get('/{id}:int/edit', function ($id) {

		require 'EditSquad.php';
	});

	$this->post('/{id}:int/edit', function ($id) {

		require 'EditSquadPost.php';
	});
}

if ($access == "Admin" || $access == "Coach") {
	$this->group('/moves', function () {
		require 'moves/router.php';
	});
}

if ($access == "Admin") {
	// Add a squad
	$this->get('/new', function () {

		require 'AddSquad.php';
	});

	$this->post('/new', function () {

		require 'AddSquadPost.php';
	});
}
