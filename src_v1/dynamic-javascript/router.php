<?php

$this->before('/js', function () {
  header("content-type: application/x-javascript");
});

$this->get('/gala-checkout.js', function() {
  include 'gala-checkout.php.js';
});

$this->get('/gala-entry-form.js', function() {
  include 'gala-entry-form.php.js';
});

$this->get('/add-payment-card.js', function() {
  include 'add-payment-card.php.js';
});

$this->get('/payment-helpers.js', function() {
  include 'payment-helpers.php.js';
});

$this->group('/charts', function() {
  $this->get('/squad.js', function() {
    include BASE_PATH . 'controllers/squads/squad-charts.php.js';
  });
});

$this->group('/squad-reps', function() {
  $this->get('/select.js', function() {
    include BASE_PATH . 'controllers/galas/squad-reps-and-team-managers/list.php.js';
  });
});

$this->group('/galas', function() {
  $this->get('/refund-entries.js', function() {
    include BASE_PATH . 'controllers/payments/galas/RefundCharge.js';
  });
});

$this->group('/users', function() {
  $this->get('/list.js', function() {
    include BASE_PATH . 'controllers/users/list.js';
  });
});

$this->group('/attendance', function() {
  $this->get('/sessions.js', function() {
    include BASE_PATH . 'controllers/attendance/sessions.js';
  });

  $this->get('/edit-end-date.js', function() {
    include 'attendance/sessions/edit-end-date.js';
  });
});

$this->get('/*', function() {
  header("content-type: text/html");
  halt(404);
});