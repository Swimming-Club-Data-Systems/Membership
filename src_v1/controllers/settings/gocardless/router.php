<?php

$this->get('/', function() {
  include 'home.php';
});

$this->get('/register', function() {
  include 'register.php';
});