<?php

$this->any('/', function() {
	
	require 'webhooks/handler.php';
});
