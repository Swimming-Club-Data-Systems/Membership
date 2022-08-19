<?php

if (app()->user && app()->user->hasPermission('Admin')) {
	$this->get('/', function () {
		include 'admin/home.php';
	});

	$this->get('/{id}:int', function ($id) {
		include 'admin/list.php';
	});
}
