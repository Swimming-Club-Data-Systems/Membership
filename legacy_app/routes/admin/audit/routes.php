<?php

$this->get('/', function () {
  include BASE_PATH . 'controllers/global-admin/audit/home.php';
});

$this->get('/logs', function () {
  include BASE_PATH . 'controllers/global-admin/audit/logs.php';
});