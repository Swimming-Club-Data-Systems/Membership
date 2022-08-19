<?php

$this->get('/', function () {
  if (app()->user->hasPermission('SCDSPaymentsManager')) {
    include 'redirect.php';
  } else {
    include 'no-access.php';
  }
});

$this->group('/setup-direct-debit', function () {
  $this->get('/', function () {
    if (app()->user->hasPermission('SCDSPaymentsManager')) {
      include 'direct-debit-setup.php';
    } else {
      include 'no-access.php';
    }
  });

  $this->get('/success', function () {
    if (app()->user->hasPermission('SCDSPaymentsManager')) {
      include 'direct-debit-setup-success.php';
    } else {
      include 'no-access.php';
    }
  });
});
