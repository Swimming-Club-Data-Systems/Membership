<?php

/**
 * API
 */

$this->before('/api', function (){
  header("content-type: application/json");
});

$this->group('/settings', function() {
  $this->get('/tenant', function() {
    include 'settings/tenant.php';
  });

  $this->get('/user', function() {
    include 'settings/user.php';
  });
});

$this->group('/galas', function() {
  $this->get('/', function() {
    include 'galas/galas.php';
  });

  $this->get('/{id}:int', function($id) {
    include 'galas/gala.php';
  });
});

$this->group('/react', function() {
  $this->get('/header-footer', function() {
    include 'react/header-footer.php';
  });
});

$this->group('/utilities', function() {
  $this->post('/pwned-password-check', function() {
    include 'utilities/pwned-password-check.php';
  });
});

$this->group('/admin', function() {
  $this->group('/reports', function() {
    $this->get('/league-members-report', function() {
      include 'admin/reports/league-members-report.php';
    });
  });
});

$this->group('/my-account', function() {
  $this->group('/webauthn', function() {
    $this->post('/challenge', function() {
      include 'my-account/webauthn/challenge.php';
    });

    $this->post('/register', function() {
      include 'my-account/webauthn/register.php';
    });

    $this->post('/delete', function() {
      include 'my-account/webauthn/delete.php';
    });

    $this->post('/auth-challenge', function() {
      include 'my-account/webauthn/auth-challenge.php';
    });

    $this->post('/auth-verify', function() {
      include 'my-account/webauthn/auth-verify.php';
    });

    $this->get('/authenticators', function() {
      include 'my-account/webauthn/authenticators.php';
    });
  });
});

$this->get('/test', function() {
  echo json_encode('Yo');
});

$this->any(['/', '/*'], function() {
  include 'catch-all.php';
});