<?php

$this->get('/', function($id) {
	include 'best-times.php';
});

$this->get('/event', function($id) {
	include 'event.php';
});