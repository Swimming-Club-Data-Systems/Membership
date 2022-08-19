<?php

$this->get('/', function () {
  include BASE_PATH . 'controllers/support/home.php';
});

$this->get(['/*'], function () {
  include BASE_PATH . 'controllers/support/documentation-viewer.php';
});