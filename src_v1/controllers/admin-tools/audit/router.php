<?php

$this->get('/', function () {
  include 'home.php';
});

$this->get('/logs', function () {
  include 'logs.php';
});