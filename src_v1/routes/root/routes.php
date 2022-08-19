<?php

$this->get('/', function() {
  include BASE_PATH . 'views/root/home.php';
});

$this->group('/help-and-support', function() {
  include BASE_PATH . 'routes/support/routes.php';
});

$this->group('/admin', function() {
  include BASE_PATH . 'routes/admin/routes.php';
});

$this->group('/payments-admin', function() {
  include BASE_PATH . 'controllers/admin-tools/scds-payments/admin/router.php';
});

$this->group('/account', function() {
  include BASE_PATH . 'routes/login/routes.php';
});

$this->group('/services', function() {
  include BASE_PATH . 'routes/services/routes.php';
});

$this->group('/shared-services', function() {
  include BASE_PATH . 'controllers/shared-services/routes.php';
});

$this->group('/cloudflare-error-pages', function() {
  include 'cloudflare-error-pages.php';
});

$this->group(['/tenants', '/clubs'], function() {
  include 'tenants.php';
});

$this->get('/public/*', function() {
  $filename = $this[0];
  require BASE_PATH . 'controllers/PublicFileLoader.php';
});

$this->group('/db', function () {
  // Handle database migrations
  include BASE_PATH . 'controllers/migrations/router.php';
});

$this->any('/*', function() {
  $filename = $this[0];
  require BASE_PATH . 'controllers/PublicFileLoader.php';
  // include BASE_PATH . 'views/root/errors/404.php';
});