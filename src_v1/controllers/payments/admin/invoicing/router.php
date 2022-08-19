<?php

$this->get('/', function() {
  require 'Home.php';
});

$this->get('/help', function() {
  require 'Help.php';
});

$this->get('/new', function() {
  require 'NewPayments.php';
});

$this->post('/new/get-user', function() {
  require 'GetUser.php';
});

$this->post('/new/search-users', function() {
  require 'SearchUsers.php';
});

$this->post('/new', function() {
  require 'NewPaymentsPost.php';
});