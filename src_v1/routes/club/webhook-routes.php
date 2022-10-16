<?php

$db = app()->db;
$tenant = app()->tenant;

if (bool(getenv('IS_DEV'))) {
  $this->group('/dev', function () {
    include BASE_PATH . 'controllers/dev/router.php';
  });
}

$this->get('/emergency-message.json', function () {
  include BASE_PATH . 'controllers/public/emergency-message.json.php';
});

$this->post('/check-login.json', function () {
  header("content-type: application/json");
  echo json_encode(['signed_in' => isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])]);
});

$this->get('/robots.txt', function () {
  header("Content-Type: text/plain");
  echo "User-agent: *\r\nDisallow: /webhooks/\r\nDisallow: /webhooks\r\nDisallow: /css\r\nDisallow: /js\r\nDisallow: /public\r\nDisallow: /files";
});

if (getenv('MAINTENANCE')) {
  $this->any(['/', '/*'], function () {
    halt(000);
  });
}

$this->group('/payments/webhooks', function () {
  include BASE_PATH . 'controllers/payments/webhooks.php';
});

$this->any('/payments/stripe/webhooks', function () {
  include BASE_PATH . 'controllers/payments/stripe/webhooks.php';
});

$this->group('/webhooks', function () {
  include BASE_PATH . 'controllers/webhooks/router.php';
});

$this->any(['/', '/*'], function () {
  $domain = app()->tenant->getDomain();
  if (!$domain) {
    $domain = app()->tenant->getUUID() . '.' . getenv('MAIN_DOMAIN');
  }
  http_response_code(303);
  header("Location: " . 'https://' . rtrim($domain, '/') . '/' . ltrim(str_replace(app()->tenant->getCodeID(), '', $_SERVER['REQUEST_URI']), '/'));
});
