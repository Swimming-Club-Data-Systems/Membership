<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn']) && bool($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {
  // IF LOGGED IN
  $this->get('/', function() {
    include 'home.php';
  });

    $this->group('/locations', function() {
      if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') {
        $this->get('/', function() {
          include 'locations/list.php';
        });

        $this->get('/new', function() {
          include 'locations/new.php';
        });

        $this->post('/new', function() {
          include 'locations/new-post.php';
        });

        $this->get('/{id}:uuid/edit', function($id) {
          include 'locations/edit.php';
        });

        $this->post('/{id}:uuid/edit', function($id) {
          include 'locations/edit-post.php';
        });
      }
    });

    $this->group('/reports', function() {
    
    });
} else {
  // NOT LOGGED IN
  $this->get('/', function() {
    include 'public.php';
  });
}

$this->group('/check-in', function() {
  $this->get('/', function() {
    include 'check-in/choose-location.php';
  });

  $this->get('/{id}:uuid', function($id) {
    include 'check-in/location.php';
  });
});