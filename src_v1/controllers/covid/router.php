<?php

$this->group('/contact-tracing', function () {
  include BASE_PATH . 'controllers/contact-tracing/router.php';
});

$this->group('/health-screening', function () {
  include 'health-screen/router.php';
});

$this->group('/competition-health-screening', function () {
  include 'gala-health-screen/router.php';
});

$this->group('/risk-awareness', function () {
  include 'risk-awareness/router.php';
});

$this->get('/', function() {
  include 'home.php';
});