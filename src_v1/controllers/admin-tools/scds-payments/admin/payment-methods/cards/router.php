<?php

$this->get('/', function() {
  include 'home.php';
});

$this->get('/add', function() {
  include 'add.php';
});

$this->post('/add', function() {
  include 'add-post.php';
});

$this->get('/setup-success', function() {
  include 'success.php';
});