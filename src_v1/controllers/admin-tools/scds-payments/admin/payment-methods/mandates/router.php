<?php

$this->get('/', function() {
  include 'home.php';
});

$this->post('/', function() {
  include 'default-post.php';
});

$this->group('/set-up', function() {
  $this->get('/', function() {
    include 'setup.php';
  });
  
  $this->get('/success', function() {
    include 'success.php';
  });
});