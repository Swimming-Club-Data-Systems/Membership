<?php

$user = app()->user;

if ($user->hasPermissions(['Committee', 'Admin', 'Coach'])) {
  // Attendance Home
  $this->get('/', function () {
    include 'indexView.php';
  });

  $this->group('/venues', function () {
    $this->get('/', function () {
      include 'Venues.php';
    });

    $this->get('/new', function () {
      include 'NewVenue.php';
    });

    $this->post('/new', function () {
      include 'NewVenuePost.php';
    });

    $this->get('/{id}:int', function ($id) {
      include 'EditVenue.php';
    });

    $this->post('/{id}:int', function ($id) {
      include 'EditVenuePost.php';
    });
  });

  // Registers
  $this->group('/register', function () {
    $this->get('/', function () {
      include 'register/register.php';
    });

    $this->post('/data-post', function () {
      include 'register/register-post.php';
    });

    $this->post('/sheet', function () {
      include 'register/session-register-ajax.php';
    });

    $this->post('/sessions', function () {
      include 'register/session-drop-down-ajax.php';
    });
  });

  $this->get('/ajax/register/sessions', function () {

    include BASE_PATH . 'controllers/ajax/registerSessions.php';
  });

  // Sessions
  $this->group('/sessions', function () {


    $this->get('/', function () {
      include "sessions.php";
    });

    $this->post('/new', function () {
      include 'add-sessions/new-post.php';
    });

    $this->get('/{id}:int', function ($id) {

      include "sessionViews/editEndDate.php";
    });

    $this->post('/ajax/handler', function () {

      include BASE_PATH . "controllers/ajax/sessions.php";
    });

    $this->post('/ajax/endDateHandler', function () {

      include BASE_PATH . "controllers/ajax/sessionsEndDate.php";
    });
  });

  // History
  $this->group('/history', function () {


    $this->get('/', function () {

      include "historyViews/history.php";
    });

    $this->get('/squads', function () {
      include "historyViews/squads.php";
    });

    // $this->get('/squads/{id}:int', function($id) {
    //   include "historyViews/squadHistory.php";
    // });

    $this->get('/squads/{id}:int/week', function ($id) {
      include "historyViews/squad-history.php";
    });

    $this->post('/squads/{id}:int/week/jump-to-week', function ($id) {
      include "historyViews/jump-to-week.php";
    });

    $this->get('/squads/{id}:int/search', function ($id) {
      include "historyViews/squad-search.php";
    });

    $this->post('/squads/search', function () {
      include "historyViews/squad-search-ajax.php";
    });

    $this->get(['/members', '/swimmers'], function () {
      include "historyViews/swimmers.php";
    });

    $this->get('/members/{id}:int/search', function ($id) {
      include "historyViews/member-search.php";
    });

    $this->post('/members/search', function () {
      include "historyViews/member-search-ajax.php";
    });

    $this->post('/ajax/swimmers', function () {
      include BASE_PATH . "controllers/ajax/swimmerHistory.php";
    });

    $this->get(['/members/{id}:int', '/swimmers/{id}:int'], function ($id) {

      include "historyViews/swimmerHistory.php";
    });
  });
}

$this->group('/booking', function () {
  include 'booking/router.php';
});
