<?php

$this->get('/', function() {
  include 'home.php';
});

$this->post('/', function() {
  include 'home-post.php';
});

$this->group('/new', function() {
  $this->get('/', function() {
    include 'new.php';
  });

  $this->post('/', function() {
    include 'new-post.php';
  });
});

$this->group('/{id}:uuid', function($id) {
  $this->get('/', function($id) {
    include 'edit.php';
  });

  $this->post('/', function($id) {
    include 'edit-post.php';
  });
});