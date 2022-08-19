<?php

$this->get('/', function () {
  include 'moves.php';
});

$this->get('/new', function () {
  include 'new-move.php';
});
