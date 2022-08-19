<?php

$this->get('/', function() {
  include 'sessions.php';
});

$this->post('/jump-to-week', function() {
  include 'jump-to-week.php';
});

$this->group('/booking', function () {
  include BASE_PATH . 'controllers/attendance/booking/router.php';
});
