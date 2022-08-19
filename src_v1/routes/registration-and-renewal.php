<?php

$this->get(['/', '/*'], function() {
  include BASE_PATH . 'views/registration-and-renewal.php';
});