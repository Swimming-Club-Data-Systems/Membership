<?php

$this->get('/', function() {
  include BASE_PATH . 'views/root/home.php';
});

$this->group('/shared-services', function() {
  include BASE_PATH . 'controllers/shared-services/router.php';
});

$this->group(['/tenants', '/clubs'], function() {
  include 'tenants.php';
});

$this->get('/public/*', function() {
  $filename = $this[0];
  require BASE_PATH . 'controllers/PublicFileLoader.php';
});

$this->any('/*', function() {
  include BASE_PATH . 'views/root/errors/404.php';
});