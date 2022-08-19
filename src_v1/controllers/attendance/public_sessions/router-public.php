<?php

$this->get('/', function () {
  include 'sessions.php';
});

$this->post('/jump-to-week', function () {
  include 'jump-to-week.php';
});

$this->get('/booking/book', function () {
  if (isset(app()->user)) {
    include BASE_PATH . 'controllers/attendance/booking/new-or-existing-handler.php';
  } else {
    include BASE_PATH . 'controllers/attendance/booking/book-session/book-session-public.php';
  }
});
