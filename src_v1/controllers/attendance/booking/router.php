<?php

$this->get('/', function () {
  include 'home.php';
});

$this->get('/my-bookings', function () {
  include 'my-bookings/my-bookings.php';
});

$this->get('/book', function () {
  include 'new-or-existing-handler.php';
});

$this->post('/book', function () {
  include 'book-session/book-session-post.php';
});

$this->get('/my-booking-info', function () {
  include 'book-session/my-members/ajax-my-members.php';
});

$this->get('/all-booking-info', function () {
  include 'book-session/all-members/ajax-all-members.php';
});

$this->post('/cancel', function () {
  include 'book-session/cancel-booking-post.php';
});

$this->post('/require-booking', function () {
  include 'require-booking/require-booking-post.php';
});

$this->get('/edit', function () {
  include 'require-booking/edit-require-booking.php';
});

$this->post('/edit', function () {
  include 'require-booking/edit-require-booking-post.php';
});

$this->post('/book', function () {
  include 'require-booking/edit-require-booking-post.php';
});

$this->group('/book-on-behalf-of', function () {
  $this->get('/', function () {
    include 'book-session/book-on-behalf-of.php';
  });

  $this->post('/', function () {
    include 'book-session/book-on-behalf-of-post.php';
  });
});
