<?php

$this->get('/', function() {
  include 'edit.php';
});

$this->post('/', function() {
  include 'edit-post.php';
});

$this->get('/members', function() {
  include 'members.php';
});
