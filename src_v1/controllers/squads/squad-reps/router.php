<?php

$this->get('/', function() {
  include 'page-selector.php';
});

$this->get('/list', function() {
  include 'public-rep-list.php';
});

$this->group('/contact-details', function() {
  $this->get('/', function() {
    include 'contact-details.php';
  });

  $this->post('/', function() {
    include 'contact-details-post.php';
  });
});