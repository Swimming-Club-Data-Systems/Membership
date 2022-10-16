<?php

$this->get('/', function () {
  include 'home.php';
});

$this->post('/', function () {
  include 'new-post.php';
});