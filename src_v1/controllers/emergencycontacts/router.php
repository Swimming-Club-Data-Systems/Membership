<?php

$this->get('/', function() {
	
	require('parents/index.php');
});

$this->get('/edit/{id}:int', function($id) {
	
	require('parents/edit.php');
});

$this->post('/edit/{id}:int', function($id) {
	
	require('parents/editUpdate.php');
});

$this->get('/new', function() {
	
	require('parents/new.php');
});

$this->post('/new', function() {
	
	require('parents/newAction.php');
});

$this->get('/{id}:int/delete', function($id) {
	
	require('parents/delete.php');
});
