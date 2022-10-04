<?php

$this->get('/', function () {
  include BASE_PATH . 'controllers/checkout/home.php';
});

$this->group('/v1', function () {
  $this->get('/{id}:uuid', function ($id) {
    include BASE_PATH . 'controllers/checkout/v1/checkout-decide.php';
  });
});