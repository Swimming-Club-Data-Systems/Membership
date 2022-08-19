<?php

$this->get('/', function() {
  $url_path = 'trials/parents/';
  include 'TrialRequests.php';
});

$this->get('/accepted', function() {
  $url_path = 'trials/parents/';
  include 'AcceptedSwimmers.php';
});

$this->get(['/requests/{request}:int', '/{request}:int/request', '/{request}:int'], function($request) {
  $url_path = 'trials/parents/';
  include 'TrialRequest.php';
});

$this->post(['/requests/{request}:int', '/{request}:int/request', '/{request}:int'], function($request) {
  $url_path = 'trials/parents/';
  include 'TrialRequestPost.php';
});

$this->get(['/recommendations/{request}:int', '/{request}:int/recommendations'], function($request) {
  $url_path = 'trials/parents/';
  include 'TrialRecommendations.php';
});

$this->post(['/recommendations/{request}:int', '/{request}:int/recommendations'], function($request) {
  $url_path = 'trials/parents/';
  include 'TrialRecommendationsPost.php';
});

$this->get(['/parents/{hash}/status', '/parents/{hash}'], function($hash) {
  $url_path = 'trials/parents/';
  $use_membership_menu = true;
  include BASE_PATH . 'controllers/services/RequestTrialStatus.php';
});

$this->get('/parents/{hash}/invite', function($hash) {
  $url_path = 'trials/parents/';
  include 'BeginInvite.php';
});

$this->post('/parents/{hash}/invite', function($hash) {
  $url_path = 'trials/parents/';
  include 'BeginInvitePost.php';
});

$this->get('/parents/{hash}/cancel/{trial}', function($hash, $trial) {
  $url_path = 'trials/parents/';
  $use_membership_menu = true;
  include BASE_PATH . 'controllers/services/DeleteTrialRequest.php';
});
