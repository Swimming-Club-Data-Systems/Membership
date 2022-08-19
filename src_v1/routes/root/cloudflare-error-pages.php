<?php

$this->get('/ip-country-block', function() {
  include BASE_PATH . 'views/root/cloudflare-errors/ip-country-block.php';
});

$this->get('/waf-block', function() {
  include BASE_PATH . 'views/root/cloudflare-errors/waf-block.php';
});

$this->get('/500-error', function() {
  include BASE_PATH . 'views/root/cloudflare-errors/500-error.php';
});

$this->get('/1000-error', function() {
  include BASE_PATH . 'views/root/cloudflare-errors/1000-error.php';
});

$this->get('/security-challenge', function() {
  include BASE_PATH . 'views/root/cloudflare-errors/security-challenge.php';
});

$this->get('/under-attack-challenge', function() {
  include BASE_PATH . 'views/root/cloudflare-errors/under-attack-challenge.php';
});