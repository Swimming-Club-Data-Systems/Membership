<?php

$this->get('/', function() {
  include 'home.php';
});

$this->get('/select-payment', function() {
  include 'select-payment.php';
});

$this->get('/more-details', function() {
  include 'more-details.php';
});

$this->get('/confirm-selected', function() {
  include 'confirm-selected.php';
});

$this->post('/form-1', function() {
  include 'form-main-handler.php';
});

$this->post('/form-2', function() {
  include 'form-more-details-handler.php';
});