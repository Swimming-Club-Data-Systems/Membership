<?php

$this->get('/', function () {
  include 'home.php';
});

$this->get('/{id}:uuid', function ($id) {
  include 'view.php';
});
