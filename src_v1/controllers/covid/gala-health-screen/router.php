<?php

$this->get('/', function() {
  include 'home.php';
});

$this->get('/any', function() {
  include 'any.php';
});

$this->get('/new-survey', function() {
  include 'screening-survey/survey.php';
});

$this->post('/new-survey', function() {
  include 'screening-survey/survey-post.php';
});

$this->group('/members', function () {
  $this->get('/{id}:int', function($id) {
    include 'submissions/member.php';
  });
});

$this->group('/galas', function () {
  $this->get('/{id}:int', function($id) {
    include 'submissions/gala.php';
  });
});

$this->group('/squads', function () {
  $this->get('/{id}:int', function($id) {
    include 'submissions/squad.php';
  });
});

$this->post('/approval', function() {
  include 'submissions/submission-auth.php';
});

$this->post('/void', function() {
  include 'submissions/void.php';
});