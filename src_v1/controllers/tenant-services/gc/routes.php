<?php

$this->get('/', function() {
  echo 'HI';
});

$this->get('/connect-account', function() {
  include 'connect-account.php';
});