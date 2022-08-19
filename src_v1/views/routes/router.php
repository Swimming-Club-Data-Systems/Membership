<?php

$this->get('/main-menu.json', function() {
  include BASE_PATH . 'views/menus/main.json.php';
});