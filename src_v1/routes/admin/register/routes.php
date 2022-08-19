<?php

$this->get('/', function() {
  include BASE_PATH . 'views/register/club.php';
});

$this->post('/', function() {
  include BASE_PATH . 'controllers/tenants/register/new.php';
});