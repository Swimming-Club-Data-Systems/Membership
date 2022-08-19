<?php

$this->get('/', function() {
  include 'home.php';
});

$this->get('/run', function() {
  include 'migrate.php';
});