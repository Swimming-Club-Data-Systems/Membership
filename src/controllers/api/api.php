<?php

/**
 * API
 */

$this->before('/api', function () {
  header("content-type: application/json");
});

$this->group('/settings', function () {
  $this->get('/tenant', function () {
    include 'settings/tenant.php';
  });

  $this->get('/user', function () {
    include 'settings/user.php';
  });
});

$this->group('/galas', function () {
  $this->get('/', function () {
    include 'galas/galas.php';
  });

  $this->get('/{id}:int', function ($id) {
    include 'galas/gala.php';
  });
});

$this->group('/react', function () {
  $this->get('/header-footer', function () {
    include 'react/header-footer.php';
  });
});

$this->group('/utilities', function () {
  $this->post('/pwned-password-check', function () {
    include 'utilities/pwned-password-check.php';
  });
});

$this->group('/admin', function () {
  $this->group('/reports', function () {
    $this->get('/league-members-report', function () {
      include 'admin/reports/league-members-report.php';
    });
  });
});

$this->group('/members', function () {
  $this->get('/orphans', function () {
    include 'members/orphans.php';
  });
});

$this->group('/onboarding', function () {
  $this->get('/check-user', function () {
    include 'onboarding/check-user.php';
  });
});

$this->get('/test', function () {
  echo json_encode('Yo');
});

$this->any(['/', '/*'], function () {
  include 'catch-all.php';
});
