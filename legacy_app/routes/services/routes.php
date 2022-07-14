<?php

$this->get('/', function() {
  include BASE_PATH . 'views/root/home.php';
});

$this->group('/gc', function() {
  $this->get('redirect', function() {
    include BASE_PATH . 'controllers/settings/gocardless/register-redirect.php';
  });
});

$this->group('/stripe', function() {
  $this->get('connect', function() {
    include BASE_PATH . 'controllers/settings/stripe/register-start.php';
  });

  $this->post('connect', function() {
    include BASE_PATH . 'controllers/settings/stripe/register-start-post.php';
  });

  $this->get('redirect', function() {
    include BASE_PATH . 'controllers/settings/stripe/register-redirect.php';
  });
});