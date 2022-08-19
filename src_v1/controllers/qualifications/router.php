<?php

$this->get('/', function () {
  include 'home.php';
});

$this->group('/new', function () {
  $this->get('/', function () {
    include 'new.php';
  });

  $this->post('/', function () {
    include 'new-post.php';
  });
});

$this->group('/{id}:uuid', function ($id) {
  $this->get('/', function ($id) {
    include 'view.php';
  });

  $this->get('/edit', function ($id) {
    include 'edit.php';
  });

  $this->post('/edit', function ($id) {
    include 'edit-post.php';
  });
});

$this->post('/lookup', function () {
  include 'add-lookup.php';
});