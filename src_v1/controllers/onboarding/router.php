<?php

$this->get('/', function () {
  include 'home.php';
});

$this->get('/go-to-session', function () {
  include 'go-to-session.php';
});

$this->get('/view-sessions', function () {
  include 'admin/view-sessions.php';
});

$this->group('/new', function () {
  $this->get('/', function () {
    include 'new/start.php';
  });

  $this->post('/', function () {
    include 'new/start-post.php';
  });

  $this->post('/user-lookup', function () {
    include 'new/user-lookup.php';
  });
});

$this->group('/sessions', function () {
  $this->group('/a', function () {
    $this->get('/{id}:uuid', function ($id) {
      include 'admin/session.php';
    });

    $this->post('/{id}:uuid', function ($id) {
      include 'admin/session-post.php';
    });

    $this->get('/{id}:uuid/batch', function ($id) {
      include 'admin/batch.php';
    });

    $this->post('/{id}:uuid/batch', function ($id) {
      include 'admin/batch-post.php';
    });
  });

  $this->get('/{id}:uuid', function ($id) {
    include 'user/session.php';
  });
});

$this->get('/all', function () {
  include 'admin/list.php';
});
