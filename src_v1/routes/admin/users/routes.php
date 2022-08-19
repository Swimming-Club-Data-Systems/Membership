<?php

$this->get('/', function() {
  include BASE_PATH . 'controllers/global-admin/users/search.php';
});