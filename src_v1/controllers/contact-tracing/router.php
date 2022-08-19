<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {
  // IF LOGGED IN
  $this->get('/', function () {
    include 'home.php';
  });

  $this->group('/locations', function () {
    if (app()->user->hasPermission('Admin')) {
      $this->get('/new', function () {
        include 'locations/new.php';
      });

      $this->post('/new', function () {
        include 'locations/new-post.php';
      });

      $this->get('/{id}:uuid/edit', function ($id) {
        include 'locations/edit.php';
      });

      $this->post('/{id}:uuid/edit', function ($id) {
        include 'locations/edit-post.php';
      });
    }
    $this->get('/', function () {
      include 'locations/list.php';
    });

    $this->get('/{id}:uuid', function ($id) {
      include 'locations/info.php';
    });

    $this->get('/{id}:uuid/poster', function ($id) {
      include 'locations/poster.php';
    });
  });

  $this->group('/reports', function () {
    if (app()->user->hasPermission('Admin')) {
      $this->get('/', function () {
        include 'reports/home.php';
      });

      $this->get('/go', function () {
        include 'reports/generate.php';
      });

      $this->get('/from-register', function () {
        include 'reports/from-register.php';
      });
    }
  });
} else {
  // NOT LOGGED IN
  $this->get('/', function () {
    include 'public.php';
  });
}

$this->group('/check-in', function () {
  $this->get('/', function () {
    include 'check-in/choose-location.php';
  });

  $this->get('/{id}:uuid', function ($id) {
    if (isset($_GET['squad'])) {
      include 'check-in/squad-registers/location.php';
    } else {
      include 'check-in/location.php';
    }
  });

  $this->post('/{id}:uuid', function ($id) {
    include 'check-in/location-post.php';
  });

  $this->post('/{id}:uuid/squad-register', function ($id) {
    include 'check-in/squad-registers/location-post.php';
  });

  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingSuccess']) {
    $this->get('/{id}:uuid/success', function ($id) {
      include 'check-in/success.php';
    });
  }
});

$this->group('/sign-out', function () {
  $this->get('/', function () {
    include 'sign-out/choose-location.php';
  });

  $this->get('/{id}:uuid', function ($id) {
    include 'sign-out/location.php';
  });

  $this->post('/ajax', function () {
    include 'sign-out/location-post.php';
  });

  if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ContactTracingSuccess']) {
    $this->get('/{id}:uuid/success', function ($id) {
      include 'sign-out/success.php';
    });
  }
});
