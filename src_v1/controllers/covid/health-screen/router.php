<?php

$this->get('/', function() {
  include 'home.php';
});

$this->group('/members', function () {
  $this->get('/{id}:int', function($id) {
    include 'submissions/member.php';
  });

  $this->get('/{id}:int/new-survey', function($id) {
    include 'screening-survey/survey.php';
  });

  $this->post('/{id}:int/new-survey', function($id) {
    include 'screening-survey/survey-post.php';
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