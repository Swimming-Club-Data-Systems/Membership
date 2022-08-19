<?php

$this->get('/', function () {
  include BASE_PATH . 'controllers/global-admin/notify/home.php';
});

$this->get('/history', function () {
  include BASE_PATH . 'controllers/global-admin/notify/email-history.php';
});

$this->group('/compose', function () {
  $this->get('/', function () {
    include BASE_PATH . 'controllers/global-admin/notify/compose.php';
  });

  $this->post('/', function () {
    include BASE_PATH . 'controllers/global-admin/notify/compose-post.php';
  });
});
