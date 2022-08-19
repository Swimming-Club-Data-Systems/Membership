<?php

$this->get('/', function() {
  include 'home.php';
});

$this->group('/members', function () {
  $this->get('/{id}:int/new-form', function($id) {
    include 'risk-form/form.php';
  });

  $this->post('/{id}:int/new-form', function($id) {
    include 'risk-form/form-post.php';
  });
});

$this->group('/squads', function () {
  $this->get('/{id}:int', function($id) {
    include 'submissions/squad.php';
  });
});

$this->post('/void', function() {
  include 'submissions/void.php';
});