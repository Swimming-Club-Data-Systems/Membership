<?php

$this->get('/', function() {
  include 'home.php';
});

$this->post('/', function() {
  include 'home-post.php';
});

$this->get('/register', function() {
  include 'register.php';
});