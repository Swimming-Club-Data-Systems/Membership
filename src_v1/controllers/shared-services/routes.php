<?php

$this->group('/gc', function() {
  include 'gc/routes.php';
});

$this->group('/stripe', function() {
  include 'stripe/routes.php';
});