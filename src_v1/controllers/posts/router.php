<?php

use Respect\Validation\Validator as v;

if (app()->user->hasPermission('Admin')) {
	$this->get('/new', function () {
		include 'NewPost.php';
	});

	$this->post('/new', function () {
		include 'NewPostServer.php';
	});

	$this->get('/{id}:int/edit', function ($id) {
		include 'EditPost.php';
	});

	$this->post('/{id}:int/edit', function ($id) {
		include 'EditPostServer.php';
	});
}

$this->get('/', function () {
	if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Parent") {
		header("Location: " . autoUrl(""));
	} else {
		include 'PostList.php';
	}
});

$this->get('/{id}:int', function ($id) {
	$int = true;
	include 'Post.php';
});


$this->get('/{id}:int/print.pdf', function ($id) {
	include 'PrintPost.php';
});

$this->get(['/*'], function () {
	$int = false;
	$id = ltrim($this[0], '/');
	include 'Post.php';
});
